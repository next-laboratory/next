<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 * 这两个方法来自symfony/class-loader
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Di;

use Composer\Autoload\ClassLoader;
use Max\Di\Annotations\Aspect;
use Max\Di\Annotations\MethodAnnotation;
use Max\Di\Contracts\ClassAttribute;
use Max\Di\Contracts\MethodAttribute;
use Max\Utils\Filesystem;
use Psr\Container\ContainerExceptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use ReflectionMethod;
use function in_array;
use const PATHINFO_EXTENSION;
use const T_CLASS;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_DOUBLE_COLON;
use const T_INTERFACE;
use const T_NAMESPACE;
use const T_NS_SEPARATOR;
use const T_STRING;
use const T_TRAIT;
use const T_WHITESPACE;

class Scanner
{
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
     * Extract the classes in the given file.
     *
     * @param string $path The file to check
     *
     * @return array The found classes
     */
    private static function findClasses(string $path): array
    {
        $contents                   = file_get_contents($path);
        $tokens                     = token_get_all($contents);
        $nsTokens                   = [T_STRING => true, T_NS_SEPARATOR => true];
        $nsTokens[T_NAME_QUALIFIED] = true;
        $classes                    = [];
        $namespace                  = '';
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }
            $class = '';
            switch ($token[0]) {
                case T_NAMESPACE:
                    $namespace = '';
                    // If there is a namespace, extract it
                    while (isset($tokens[++$i][1])) {
                        if (isset($nsTokens[$tokens[$i][0]])) {
                            $namespace .= $tokens[$i][1];
                        }
                    }
                    $namespace .= '\\';
                    break;
                case T_CLASS:
                case T_INTERFACE:
                case T_TRAIT:
                    // Skip usage of ::class constant
                    $isClassConstant = false;
                    for ($j = $i - 1; $j > 0; --$j) {
                        if (!isset($tokens[$j][1])) {
                            break;
                        }
                        if (T_DOUBLE_COLON === $tokens[$j][0]) {
                            $isClassConstant = true;
                            break;
                        } else if (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT])) {
                            break;
                        }
                    }
                    if ($isClassConstant) {
                        break;
                    }
                    // Find the classname
                    while (isset($tokens[++$i][1])) {
                        $t = $tokens[$i];
                        if (T_STRING === $t[0]) {
                            $class .= $t[1];
                        } else if ('' !== $class && T_WHITESPACE === $t[0]) {
                            break;
                        }
                    }
                    $classes[ltrim($namespace . $class, '\\')] = $path;
                    break;
                default:
                    break;
            }
        }
        return $classes;
    }

    /**
     * @param ClassLoader $loader
     * @param string      $proxyPath 代理类地图
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    public static function init(ClassLoader $loader, string $proxyPath)
    {
        file_exists($proxyPath) && unlink($proxyPath);
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new \Exception('Process fork failed.');
        }
        pcntl_wait($pid);
        $proxies = self::proxy($proxyPath);
        $loader->addClassMap($proxies);
        self::scanAnnotations();
    }

    /**
     * @param string $proxiesMap
     *
     * @return mixed|void
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    protected static function proxy(string $proxiesMap)
    {
        if (!file_exists($proxiesMap)) {
            $proxyDir = BASE_PATH . 'runtime/proxy/';
            file_exists($proxyDir) || mkdir($proxyDir, 0755, true);
            (new Filesystem())->cleanDirectory($proxyDir);
            $classMap = self::scanAnnotations();
            $scanMap  = [];
            foreach ($classMap as $class => $path) {
                $reflectionClass = ReflectionManager::reflectClass($class);
                $proxyPath       = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                $codeString      = file_get_contents($path);
                $file            = new \SplFileObject($path, 'r');
                $replacement     = [];
                foreach (AnnotationManager::getMethodsAnnotations() as $method) {
                    $reflectionMethod = ReflectionManager::reflectMethod($class, $method);
                    $startLine        = $reflectionMethod->getStartline();
                    $file->seek($startLine - 1);
                    preg_match('/function[\s\w]+\((.*)\)/', $file->getCurrentLine(), $matches);
                    $params  = $matches[1] ?? '';
                    $endLine = $reflectionMethod->getEndLine();
                    $file->seek($startLine + 1);
                    $code = '';
                    while ($startLine++ < $endLine - 2) {
                        $code .= $file->fgets();
                    }
                    $newCode            = str_replace('    ', '      ', $code);
                    $return             = ($reflectionMethod->getReturnType() == 'void') ? '' : 'return ';
                    $replacement[$code] = "        $return\$this->__callViaProxy(__FUNCTION__, function ($params) {\n$newCode        }, func_get_args());\n";
                }

                $constructor = $reflectionClass->hasMethod('__construct') ? '' : "\n    public function __construct() \n    {\n        \$this->__handleProperties();\n    }\n";
                $codeString  = preg_replace('/\{/', <<<EOR
                {
                    use \\Max\\Di\\Aop\\Traits\\ProxyHandler;
                    use \\Max\\Di\\Aop\\Traits\\PropertyHandler;
                    $constructor
                EOR
                    , $codeString, 1);
                file_put_contents($proxyPath, str_replace(array_keys($replacement), array_values($replacement), $codeString));
                $scanMap[$class] = $proxyPath;
            }

            file_put_contents($proxiesMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include $proxiesMap;
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws Exceptions\NotFoundException
     * @throws ReflectionException
     */
    protected static function scanAnnotations(): array
    {
        $proxies = [];
        foreach (config('di.scanDir') as $dir) {
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
