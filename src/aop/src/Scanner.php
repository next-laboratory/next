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
use Max\Di\Reflection;
use Max\Utils\Filesystem;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Throwable;

final class Scanner
{
    protected static ClassLoader $loader;
    protected static AstManager  $astManager;
    protected static string      $runtimeDir;
    protected static string      $proxyMap;
    protected static array       $classMap    = [];
    protected static array       $collectors  = [AspectCollector::class, PropertyAttributeCollector::class];
    protected static bool        $initialized = false;

    /**
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, ScannerConfig $config): void
    {
        if (!self::$initialized) {
            self::$loader     = $loader;
            self::$runtimeDir = $config->getRuntimeDir() . '/aop/';
            Filesystem::isDirectory(self::$runtimeDir) || Filesystem::makeDirectory(self::$runtimeDir, 0755, true);
            self::$astManager = new AstManager();
            self::$classMap   = self::scanDir($config->getPaths());
            self::$proxyMap   = $proxyMap = self::$runtimeDir . 'proxy.php';
            if (!$config->isCache() || !Filesystem::exists($proxyMap)) {
                Filesystem::exists($proxyMap) && Filesystem::delete($proxyMap);
                if (($pid = pcntl_fork()) == -1) {
                    throw new ProcessException('Process fork failed.');
                }
                pcntl_wait($pid);
            }
            $loader->addClassMap(self::getProxyMap(self::$collectors));
            self::collect([...self::$collectors, ...$config->getCollectors()]);
            self::$initialized = true;
        }
    }

    public static function getLoader(): ClassLoader
    {
        return self::$loader;
    }

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
     * @throws ReflectionException
     */
    protected static function getProxyMap(array $collectors): array
    {
        if (!Filesystem::exists(self::$proxyMap)) {
            $proxyDir = self::$runtimeDir . 'proxy/';
            Filesystem::makeDirectory($proxyDir, 0755, true, true);
            Filesystem::cleanDirectory($proxyDir);
            self::collect($collectors);
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAttributeCollector::getCollectedClasses()));
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                Filesystem::put($proxyPath, self::generateProxyClass($class, self::$classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            Filesystem::put(self::$proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include self::$proxyMap;
    }

    protected static function generateProxyClass(string $class, string $path): string
    {
        $ast       = self::$astManager->getNodes($path);
        $traverser = new NodeTraverser();
        $metadata  = new Metadata(self::$loader, $class);
        $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
        $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        $modifiedStmts = $traverser->traverse($ast);
        $prettyPrinter = new Standard;
        return $prettyPrinter->prettyPrintFile($modifiedStmts);
    }

    /**
     * @throws ReflectionException
     */
    protected static function collect(array $collectors): void
    {
        foreach (self::$classMap as $class => $path) {
            $reflectionClass = Reflection::class($class);
            // 收集类注解
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    foreach ($collectors as $collector) {
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
                        foreach ($collectors as $collector) {
                            $collector::collectProperty($class, $reflectionProperty->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
            // 收集方法注解
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        foreach ($collectors as $collector) {
                            $collector::collectMethod($class, $method, $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
                // 收集该方法的参数的注解
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    try {
                        foreach ($reflectionParameter->getAttributes() as $attribute) {
                            foreach ($collectors as $collector) {
                                $collector::collectorMethodParameter($class, $method, $reflectionParameter->getName(), $attribute->newInstance());
                            }
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
