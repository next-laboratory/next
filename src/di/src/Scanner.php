<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 * 部分方法来自symfony/class-loader
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Di;

use Composer\Autoload\ClassLoader;
use Max\Di\Aop\NodeVisitor\Metadata;
use Max\Di\Aop\NodeVisitor\PropertyHandlerVisitor;
use Max\Di\Aop\NodeVisitor\ProxyHandlerVisitor;
use Max\Di\Contracts\ClassAttribute;
use Max\Di\Contracts\MethodAttribute;
use Max\Di\Exceptions\ProcessException;
use Max\Utils\Filesystem;
use PhpParser\Error;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerExceptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;

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

    /**
     * @var Scanner
     */
    private static Scanner $scanner;

    /**
     * @param ClassLoader $loader
     * @param array       $scanDir    扫描路径
     * @param string      $runtimeDir 缓存路径
     */
    private function __construct(
        protected ClassLoader $loader,
        protected array       $scanDir,
        string                $runtimeDir)
    {
        $this->runtimeDir = $runtimeDir = rtrim($runtimeDir, '/\\') . '/di/';
        is_dir($runtimeDir) || mkdir($runtimeDir, 0755, true);
        $this->proxyMap = $proxyMap = $runtimeDir . 'proxy.php';
        file_exists($proxyMap) && unlink($proxyMap);
    }

    /**
     * 根据绝对路径扫描完整类名[一个文件只能存放一个类，否则可能解析失败]
     *
     * @param string $dir
     *
     * @return array
     */
    public static function scanDir(string $dir): array
    {
        $dir     = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        $classes = [];
        foreach ($dir as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $path = $file->getRealPath() ?: $file->getPathname();
            if ('php' !== pathinfo($path, PATHINFO_EXTENSION)) {
                continue;
            }
            $classes = array_merge($classes, self::findClasses($path));
            gc_mem_caches();
        }
        return $classes;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected static function findClasses(string $path): array
    {
        try {
            $parser  = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            $ast     = $parser->parse(file_get_contents($path));
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
     * @param array       $scanDir
     * @param string      $runtimeDir
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, array $scanDir, string $runtimeDir): void
    {
        if (!isset(self::$scanner)) {
            self::$scanner = new Scanner($loader, $scanDir, $runtimeDir);
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
            $classMap = $this->collect();
            $scanMap  = [];
            foreach ($classMap as $class => $path) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $filesystem->put($proxyPath, $this->parse($class, $path));
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
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast       = $parser->parse(file_get_contents($path));
            $traverser = new NodeTraverser;
            $metadata  = new Metadata($class);
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
     * @return array
     * @throws ReflectionException
     */
    protected function collect(): array
    {
        $proxies = [];
        foreach ($this->scanDir as $dir) {
            foreach (self::scanDir($dir) as $class => $path) {
                $proxy           = false;
                $reflectionClass = ReflectionManager::reflectClass($class);
                foreach ($reflectionClass->getAttributes() as $attribute) {
                    $instance = $attribute->newInstance();
                    if ($instance instanceof ClassAttribute) {
                        $instance->handle($reflectionClass);
                    }
                }

                foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                    foreach ($reflectionProperty->getAttributes() as $attribute) {
                        $proxy = true;
                        AnnotationManager::annotationProperty(
                            $reflectionClass->getName(), $reflectionProperty->getName(), $attribute->newInstance()
                        );
                    }
                }

                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    foreach ($reflectionMethod->getAttributes() as $attribute) {
                        $instance = $attribute->newInstance();
                        if ($instance instanceof MethodAttribute) {
                            $proxy = true;
                            $instance->handle($reflectionClass, $reflectionMethod);
                        }
                    }
                }

                if ($proxy) {
                    $proxies[$class] = $path;
                }
            }
        }
        return $proxies;
    }
}
