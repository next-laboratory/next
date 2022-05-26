<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Aop;

use Composer\Autoload\ClassLoader;
use Max\Aop\Collectors\AspectCollector;
use Max\Aop\Collectors\PropertyAttributeCollector;
use Max\Aop\Exceptions\ProcessException;
use Max\Di\ReflectionManager;
use Max\Utils\Filesystem;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;
use Throwable;

final class Scanner
{
    protected static ClassLoader $loader;
    protected static AstManager  $astManager;
    protected static Filesystem  $filesystem;
    protected static string      $runtimeDir;
    protected static string      $proxyMap;
    protected static array       $classMap    = [];
    protected static array       $collectors  = [AspectCollector::class, PropertyAttributeCollector::class];
    protected static bool        $initialized = false;

    public static function init(ClassLoader $loader, ScannerConfig $config): void
    {
        if (!self::$initialized) {
            self::$loader     = $loader;
            $filesystem       = self::$filesystem = new Filesystem();
            self::$runtimeDir = $config->getRuntimeDir() . '/aop/';
            $filesystem->isDirectory(self::$runtimeDir) || $filesystem->makeDirectory(self::$runtimeDir, 0755, true);
            array_push(self::$collectors, ...$config->getCollectors());
            self::$astManager = new AstManager();
            self::$classMap   = self::scanDir($config->getPaths());
            self::$proxyMap   = $proxyMap = self::$runtimeDir . 'proxy.php';
            if (!$config->isCache() || !$filesystem->exists($proxyMap)) {
                $filesystem->exists($proxyMap) && $filesystem->delete($proxyMap);
                if (($pid = pcntl_fork()) == -1) {
                    throw new ProcessException('Process fork failed.');
                }
                pcntl_wait($pid);
            }
            $loader->addClassMap(self::getProxyMap());
            self::collect();
            self::$initialized = true;
        }
    }

    /**
     * @param array $dirs
     *
     * @return array
     */
    public static function scanDir(array $dirs): array
    {
        $files   = (new Finder())->in($dirs)->name('*.php')->files();
        $classes = [];
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach (self::$astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }

    /**
     * @return mixed|void
     */
    protected static function getProxyMap()
    {
        if (!self::$filesystem->exists(self::$proxyMap)) {
            $proxyDir = self::$runtimeDir . 'proxy/';
            self::$filesystem->makeDirectory($proxyDir, 0755, true, true);
            self::$filesystem->cleanDirectory($proxyDir);
            self::collect();
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAttributeCollector::getCollectedClasses()));
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                self::$filesystem->put($proxyPath, self::generateProxyClass($class, self::$classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            self::$filesystem->put(self::$proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include self::$proxyMap;
    }

    /**
     * @param $class
     * @param $path
     *
     * @return string
     */
    protected static function generateProxyClass($class, $path): string
    {
        try {
            $ast       = self::$astManager->getNodes($path);
            $traverser = new NodeTraverser();
            $metadata  = new Metadata(self::$loader, $class);
            $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
            $modifiedStmts = $traverser->traverse($ast);
            $prettyPrinter = new Standard;
            return $prettyPrinter->prettyPrintFile($modifiedStmts);
        } catch (Error $error) {
            echo "[ERROR] Parse error: {$error->getMessage()}\n";
            return '';
        }
    }

    /**
     * @return void
     */
    protected static function collect(): void
    {
        foreach (self::$classMap as $class => $path) {
            $reflectionClass = ReflectionManager::reflectClass($class);
            // 收集类注解
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    foreach (self::$collectors as $collector) {
                        $collector::collectClass($class, $attribute->newInstance());
                    }
                } catch (Throwable $throwable) {
                    echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                }
            }
            //收集属性注解
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    try {
                        foreach (self::$collectors as $collector) {
                            $collector::collectProperty($class, $reflectionProperty->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
            // 收集方法注解
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        foreach (self::$collectors as $collector) {
                            $collector::collectMethod($class, $reflectionMethod->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
        }
    }

    public static function scanConfig(string $installedJsonDir): array
    {
        $installed = json_decode(file_get_contents($installedJsonDir), true);
        $installed = $installed['packages'] ?? $installed;
        $config    = [];
        foreach ($installed as $package) {
            if (isset($package['extra']['max']['config'])) {
                $configProvider = $package['extra']['max']['config'];
                $configProvider = new $configProvider;
                if (method_exists($configProvider, '__invoke')) {
                    if (is_array($configItem = $configProvider())) {
                        $config = array_merge_recursive($config, $configItem);
                    }
                }
            }
        }
        return $config;
    }
}
