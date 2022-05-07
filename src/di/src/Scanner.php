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

namespace Max\Di;

use Composer\Autoload\ClassLoader;
use Max\Di\Annotation\Collector\AspectCollector;
use Max\Di\Annotation\Collector\PropertyAttributeCollector;
use Max\Di\Aop\AstManager;
use Max\Di\Aop\Metadata;
use Max\Di\Aop\PropertyHandlerVisitor;
use Max\Di\Aop\ProxyHandlerVisitor;
use Max\Di\Exceptions\ProcessException;
use Max\Utils\Filesystem;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Throwable;

final class Scanner
{
    /**
     * @var string
     */
    protected string $runtimeDir;

    /**
     * @var array
     */
    protected array $classMap = [];

    /**
     * 注解收集器
     *
     * @var array|string[]
     */
    protected array $collectors = [
        AspectCollector::class,
        PropertyAttributeCollector::class
    ];

    /**
     * @var Scanner
     */
    private static Scanner $scanner;

    /**
     * @var string
     */
    protected string     $proxyMap;
    protected AstManager $astManager;

    /**
     * @param ClassLoader $loader
     * @param array       $options
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, array $options = []): void
    {
        if (!isset(self::$scanner)) {
            self::$scanner = new Scanner($loader, $options);
        }
    }

    /**
     * @param ClassLoader $loader
     * @param array       $options
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    private function __construct(protected ClassLoader $loader, array $options)
    {
        $this->runtimeDir = $runtimeDir = rtrim($options['runtimeDir'] ?? '', '/\\') . '/di/';
        $cache            = $options['cache'] ?? false;
        array_push($this->collectors, ...($options['collectors'] ?? []));
        is_dir($runtimeDir) || mkdir($runtimeDir, 0755, true);
        $this->astManager = new AstManager();
        $this->classMap   = $this->scanDir($options['paths'] ?? []);
        $this->proxyMap   = $proxyMap = $this->runtimeDir . 'proxy.php';
        if (!$cache || !file_exists($proxyMap)) {
            file_exists($proxyMap) && unlink($proxyMap);
            if (($pid = pcntl_fork()) == -1) {
                throw new ProcessException('Process fork failed.');
            }
            pcntl_wait($pid);
        }
        $loader->addClassMap($this->getProxyMap());
        $this->collect();
    }

    /**
     * @param array $dirs
     *
     * @return array
     */
    public function scanDir(array $dirs): array
    {
        $files   = (new Finder())->in($dirs)->name('*.php')->files();
        $classes = [];
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach ($this->astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }

    /**
     * @return mixed|void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    protected function getProxyMap()
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->proxyMap)) {
            $proxyDir = $this->runtimeDir . 'proxy/';
            $filesystem->makeDirectory($proxyDir, 0755, true, true);
            $filesystem->cleanDirectory($proxyDir);
            $this->collect();
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAttributeCollector::getCollectedClasses()));
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $filesystem->put($proxyPath, $this->generateProxyClass($class, $this->classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            $filesystem->put($this->proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include $this->proxyMap;
    }

    /**
     * @param $class
     * @param $path
     *
     * @return string
     */
    protected function generateProxyClass($class, $path): string
    {
        try {
            $ast       = $this->astManager->getNodes($path);
            $traverser = new NodeTraverser();
            $metadata  = new Metadata($this->loader, $class);
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
    protected function collect(): void
    {
        foreach ($this->classMap as $class => $path) {
            $reflectionClass = ReflectionManager::reflectClass($class);
            // 收集类注解
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    foreach ($this->collectors as $collector) {
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
                        foreach ($this->collectors as $collector) {
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
                        foreach ($this->collectors as $collector) {
                            $collector::collectMethod($class, $reflectionMethod->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
        }
    }
}
