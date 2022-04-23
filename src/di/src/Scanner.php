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
use Max\Di\Aop\Metadata;
use Max\Di\Aop\PropertyHandlerVisitor;
use Max\Di\Aop\ProxyHandlerVisitor;
use Max\Di\Exceptions\ProcessException;
use Max\Utils\Filesystem;
use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerExceptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Throwable;

final class Scanner
{
    /**
     * @var string
     */
    protected string $runtimeDir;

    /**
     * @var string
     */
    protected string $proxyMap;

    protected array $classMap = [];

    /**
     * @var array|string[]
     */
    protected array $collectors = [
        AspectCollector::class,
        PropertyAttributeCollector::class
    ];

    protected Parser $parser;

    /**
     * @var Scanner
     */
    private static Scanner $scanner;

    /**
     * @param ClassLoader $loader
     * @param array       $collectors
     * @param array       $scanDir    扫描路径
     * @param string      $runtimeDir 缓存路径
     */
    private function __construct(
        protected ClassLoader $loader,
        array                 $collectors,
        protected array       $scanDir,
        string                $runtimeDir)
    {
        $this->runtimeDir = $runtimeDir = rtrim($runtimeDir, '/\\') . '/di/';
        is_dir($runtimeDir) || mkdir($runtimeDir, 0755, true);
        $this->proxyMap = $proxyMap = $runtimeDir . 'proxy.php';
        file_exists($proxyMap) && unlink($proxyMap);
        array_push($this->collectors, ...$collectors);
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @param array $dirs
     *
     * @return void
     */
    public function scanDir(array $dirs): void
    {
        foreach ($dirs as $dir) {
            $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($dir as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $path = $file->getRealPath() ?: $file->getPathname();
                if ('php' !== pathinfo($path, PATHINFO_EXTENSION)) {
                    continue;
                }
                $this->classMap = array_merge($this->classMap, $this->findClasses($path));
                gc_mem_caches();
            }
        }
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function findClasses(string $path): array
    {
        try {
            $ast     = $this->parser->parse(file_get_contents($path));
            $classes = [];
            foreach ($ast as $stmt) {
                try {
                    if ($stmt instanceof Namespace_) {
                        $namespace = $stmt->name->toCodeString();
                        foreach ($stmt->stmts as $subStmt) {
                            if ($subStmt instanceof Class_) {
                                $classes[$namespace . '\\' . $subStmt->name->toString()] = $path;
                            }
                        }
                    }
                } catch (Error $error) {
                    echo $error->getMessage() . PHP_EOL;
                }
            }
            return $classes;
        } catch (Error $error) {
            echo $error->getMessage() . PHP_EOL;
            return [];
        }
    }

    /**
     * @param ClassLoader $loader
     * @param array       $collectors
     * @param array       $scanDir
     * @param string      $runtimeDir
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, array $collectors, array $scanDir, string $runtimeDir): void
    {
        if (!isset(self::$scanner)) {
            self::$scanner = new Scanner($loader, $collectors, $scanDir, $runtimeDir);
            if (($pid = pcntl_fork()) == -1) {
                throw new ProcessException('Process fork failed.');
            }
            pcntl_wait($pid);
            $loader->addClassMap(self::$scanner->proxy());
            self::$scanner->collect();
        }
    }

    /**
     * @return mixed|void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    protected function proxy()
    {
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->proxyMap)) {
            $proxyDir = $this->runtimeDir . 'proxy/';
            $filesystem->makeDirectory($proxyDir, 0755, true, true);
            $filesystem->cleanDirectory($proxyDir);
            $this->collect();
            $collectedClasses = array_unique([...AspectCollector::getCollectedClasses(), ...PropertyAttributeCollector::getCollectedClasses()]);
            $scanMap          = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $filesystem->put($proxyPath, $this->parse($class, $this->classMap[$class]));
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
    protected function parse($class, $path): string
    {
        try {
            $ast       = $this->parser->parse(file_get_contents($path));
            $traverser = new NodeTraverser();
            $metadata  = new Metadata($this->loader, $class);
            $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
            $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
            $modifiedStmts = $traverser->traverse($ast);
            $prettyPrinter = new Standard;
            return $prettyPrinter->prettyPrintFile($modifiedStmts);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return '';
        }
    }

    /**
     * @return void
     */
    protected function collect(): void
    {
        $this->scanDir($this->scanDir);
        foreach ($this->classMap as $class => $path) {
            $reflectionClass = ReflectionManager::reflectClass($class);
            foreach ($reflectionClass->getAttributes() as $attribute) {
                try {
                    foreach ($this->collectors as $collector) {
                        $collector::collectClass($class, $attribute->newInstance());
                    }
                } catch (Throwable $throwable) {
                    echo '[NOTICE] ' . $class . ':' . $throwable->getMessage() . PHP_EOL;
                }
            }

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    foreach ($this->collectors as $collector) {
                        $collector::collectProperty($class, $reflectionProperty->getName(), $attribute->newInstance());
                    }
                }
            }
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        foreach ($this->collectors as $collector) {
                            $collector::collectMethod($class, $reflectionMethod->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ':' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
        }
    }
}
