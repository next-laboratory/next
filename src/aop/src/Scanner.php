<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop;

use Attribute;
use Exception;
use Max\Aop\Collector\AspectCollector;
use Max\Aop\Collector\PropertyAttributeCollector;
use Max\Di\Reflection;
use Max\Utils\Composer;
use Max\Utils\Filesystem;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Throwable;

final class Scanner
{
    private AstManager $astManager;

    private string $proxyMap;

    private array $classMap = [];

    private Filesystem $filesystem;

    private static bool $initialized = false;

    private static self $scanner;

    private function __construct(
        private ScannerConfig $config
    ) {
        $this->filesystem = new Filesystem();
        $this->astManager = new AstManager();
        $runtimeDir       = $config->getRuntimeDir();
        if (!$this->filesystem->isDirectory($runtimeDir)) {
            $this->filesystem->makeDirectory($runtimeDir, 0755, true);
        }
        $this->classMap = $this->findClasses($config->getScanDirs());
        $this->proxyMap = $runtimeDir . 'proxy.php';
    }

    private function __clone(): void
    {
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public static function init(ScannerConfig $config): void
    {
        if (!self::$initialized) {
            self::$scanner     = new self($config);
            self::$initialized = true;
            self::$scanner->boot();
            Reflection::clear();
        }
    }

    /**
     * @throws Exception
     */
    public static function instance(): self
    {
        if (self::$initialized) {
            return self::$scanner;
        }
        throw new Exception('Scanner is not initialized');
    }

    public function findClasses(array $dirs): array
    {
        $files   = Finder::create()->in($dirs)->name('*.php')->files();
        $classes = [];
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach ($this->astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }

    public function addClass(string $class, string $path): void
    {
        $this->classMap[$class] = $path;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function boot(): void
    {
        if (!$this->config->isCache() || !$this->filesystem->exists($this->proxyMap)) {
            $this->filesystem->exists($this->proxyMap) && $this->filesystem->delete($this->proxyMap);
            if (($pid = pcntl_fork()) == -1) {
                throw new Exception('Process fork failed.');
            }
            pcntl_wait($pid);
        }
        $collectors = $this->config->getCollectors();
        Composer::getClassLoader()->addClassMap($this->getProxyMap($collectors));
        $this->collect($collectors);
        unset($this->filesystem, $this->astManager);
    }

    /**
     * @throws ReflectionException
     */
    private function getProxyMap(array $collectors): array
    {
        if (!$this->filesystem->exists($this->proxyMap)) {
            $proxyDir = $this->config->getRuntimeDir() . 'proxy/';
            $this->filesystem->exists($proxyDir) || $this->filesystem->makeDirectory($proxyDir, 0755, true, true);
            $this->filesystem->cleanDirectory($proxyDir);
            ob_start();
            $this->collect($collectors);
            ob_end_clean();
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAttributeCollector::getCollectedClasses()));
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $this->filesystem->put($proxyPath, $this->generateProxyClass($collectors, $class, $this->classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            $this->filesystem->put($this->proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include $this->proxyMap;
    }

    private function generateProxyClass(array $collectors, string $class, string $path): string
    {
        $traverser = new NodeTraverser();
        $metadata  = new Metadata($class);
        if (in_array(PropertyAttributeCollector::class, $collectors)) {
            $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
        }
        if (in_array(AspectCollector::class, $collectors)) {
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        }
        return (new Standard())->prettyPrintFile($traverser->traverse($this->astManager->getNodes($path)));
    }

    /**
     * @throws ReflectionException
     */
    private function collect(array $collectors): void
    {
        foreach ($this->classMap as $class => $path) {
            $reflectionClass = Reflection::class($class);
            // 收集类注解
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    $attributeInstance = $attribute->newInstance();
                    if (!$attributeInstance instanceof Attribute) {
                        foreach ($collectors as $collector) {
                            $collector::collectClass($class, $attributeInstance);
                        }
                    }
                } catch (Throwable $e) {
                    printf("\033[33m [INFO] \033[0m%s, %s\n", $class, $e->getMessage());
                }
            }
            // 收集属性注解
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $propertyName = $reflectionProperty->getName();
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    try {
                        $attributeInstance = $attribute->newInstance();
                        foreach ($collectors as $collector) {
                            $collector::collectProperty($class, $propertyName, $attributeInstance);
                        }
                    } catch (Throwable $e) {
                        printf("\033[33m [INFO] \033[0m%s->%s, %s\n", $class, $propertyName, $e->getMessage());
                    }
                }
            }
            // 收集方法注解
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        $attributeInstance = $attribute->newInstance();
                        foreach ($collectors as $collector) {
                            $collector::collectMethod($class, $method, $attributeInstance);
                        }
                    } catch (Throwable $e) {
                        printf("\033[33m [INFO] \033[0m%s, %s\n", $class, $e->getMessage());
                    }
                }
                // 收集该方法的参数的注解
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    $parameterName = $reflectionParameter->getName();
                    foreach ($reflectionParameter->getAttributes() as $attribute) {
                        try {
                            $attributeInstance = $attribute->newInstance();
                            foreach ($collectors as $collector) {
                                $collector::collectorMethodParameter($class, $method, $parameterName, $attributeInstance);
                            }
                        } catch (Throwable $e) {
                            printf("\033[33m [INFO] \033[0m%s, %s\n", $class, $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
