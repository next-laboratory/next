<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use Next\Aop\Collector\AspectCollector;
use Next\Aop\Collector\PropertyAttributeCollector;
use Next\Di\Reflection;
use Next\Utils\Composer;
use Next\Utils\Filesystem;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\Finder;

final class Aop
{
    private AstManager $astManager;

    private string $proxyMapFile;

    private static array $classMap = [];

    private Filesystem $filesystem;

    private function __construct(
        private array  $scanDirs = [],
        private array  $collectors = [],
        private string $runtimeDir = '',
    )
    {
        $this->filesystem = new Filesystem();
        $this->astManager = new AstManager();
        $this->runtimeDir = rtrim($runtimeDir, '/\\') . '/';
        if (!$this->filesystem->isDirectory($this->runtimeDir)) {
            $this->filesystem->makeDirectory($this->runtimeDir, 0755, true);
        }
        $this->findClasses($this->scanDirs);
        $this->proxyMapFile = $this->runtimeDir . 'proxy.php';
        $lockFile           = $this->runtimeDir . 'lock';
        if (file_exists($lockFile)) {
            unlink($lockFile);
            $this->getProxyMap();
        } else {
            $this->filesystem->exists($this->proxyMapFile) && $this->filesystem->delete($this->proxyMapFile);
        }
        if (!$this->filesystem->exists($this->proxyMapFile)) {
            touch($lockFile);
            $this->filesystem->exists($this->proxyMapFile) && $this->filesystem->delete($this->proxyMapFile);
            global $argv;
            $cmd    = PHP_BINARY . ' ' . implode(' ', $argv) . ' 2>&1';
            $result = shell_exec($cmd);
            if ($result) {
                print($result . PHP_EOL);
                exit(255);
            }
//            if (($pid = pcntl_fork()) == -1) {
//                throw new \RuntimeException('Process fork failed.');
//            }
//            pcntl_wait($pid);
        }
        Composer::getClassLoader()->addClassMap($this->getProxyMap());
        $this->collect();
        unset($this->filesystem, $this->astManager);
        Reflection::clear();
    }

    private function __clone(): void
    {
    }

    public static function init(
        array  $scanDirs = [],
        array  $collectors = [],
        string $runtimeDir = '',
        bool   $cache = false
    ): void
    {
        new self($scanDirs, $collectors, $runtimeDir, $cache);
    }

    public static function addClass(string $class, string $filepath): void
    {
        self::$classMap[$class] = $filepath;
    }

    private function findClasses(array $dirs): void
    {
        $files = Finder::create()->in($dirs)->name('*.php')->files();
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach ($this->astManager->getClassesByRealPath($realPath) as $class) {
                self::addClass($class, $realPath);
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function getProxyMap(): array
    {
        if (!$this->filesystem->exists($this->proxyMapFile)) {
            $proxyDir = $this->runtimeDir . 'proxy/';
            $this->filesystem->exists($proxyDir) || $this->filesystem->makeDirectory($proxyDir, 0755, true, true);
            $this->filesystem->cleanDirectory($proxyDir);
            $this->collect();
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAttributeCollector::getCollectedClasses()));
            $proxies          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $this->filesystem->put($proxyPath, $this->generateProxyClass($class));
                $proxies[$class] = $proxyPath;
            }
            $this->filesystem->put($this->proxyMapFile, sprintf("<?php \nreturn %s;", var_export($proxies, true)));
            exit(0);
        }
        return include $this->proxyMapFile;
    }

    private function generateProxyClass(string $class): string
    {
        $traverser = new NodeTraverser();
        $metadata  = new Metadata($class);
        if (in_array(PropertyAttributeCollector::class, $this->collectors)) {
            if (PropertyAttributeCollector::getByClass($class)) {
                $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
            }
        }
        if (in_array(AspectCollector::class, $this->collectors)) {
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        }
        return (new Standard())->prettyPrintFile($traverser->traverse($this->astManager->getNodes(self::$classMap[$class])));
    }

    /**
     * @throws \ReflectionException
     */
    private function collect(): void
    {
        foreach (self::$classMap as $class => $path) {
            $reflectionClass = Reflection::class($class);
            // 收集类注解
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    $attributeInstance = $attribute->newInstance();
                    if (!$attributeInstance instanceof \Attribute) {
                        foreach ($this->collectors as $collector) {
                            $collector::collectClass($class, $attributeInstance);
                        }
                    }
                } catch (\Throwable $e) {
                    printf("\033[33m [INFO] \033[0m%s: %s\n", $class, $e->getMessage());
                }
            }
            // 收集属性注解
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    try {
                        $attributeInstance = $attribute->newInstance();
                        foreach ($this->collectors as $collector) {
                            $collector::collectProperty($class, $propertyName, $attributeInstance);
                        }
                    } catch (\Throwable $e) {
                        printf("\033[33m [INFO] \033[0m%s->%s: %s\n", $class, $propertyName, $e->getMessage());
                    }
                }
            }
            // 收集方法注解
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        $attributeInstance = $attribute->newInstance();
                        foreach ($this->collectors as $collector) {
                            $collector::collectMethod($class, $method, $attributeInstance);
                        }
                    } catch (\Throwable $e) {
                        printf("\033[33m [INFO] \033[0m%s: %s\n", $class, $e->getMessage());
                    }
                }
                // 收集该方法的参数的注解
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    $parameterName = $reflectionParameter->getName();
                    foreach ($reflectionParameter->getAttributes() as $attribute) {
                        try {
                            $attributeInstance = $attribute->newInstance();
                            foreach ($this->collectors as $collector) {
                                $collector::collectorMethodParameter($class, $method, $parameterName, $attributeInstance);
                            }
                        } catch (\Throwable $e) {
                            printf("\033[33m [INFO] \033[0m%s: %s\n", $class, $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
