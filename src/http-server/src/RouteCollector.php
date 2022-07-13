<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Aop\Collectors\AbstractCollector;
use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Max\Di\Reflection;
use Max\Routing\Annotations\AutoController;
use Max\Routing\Annotations\Controller;
use Max\Routing\Contracts\MappingInterface;
use Max\Routing\Router;
use Max\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RouteCollector extends AbstractCollector
{
    /**
     * 当前控制器对应的router
     */
    protected static ?Router $router = null;

    /**
     * 当前控制器的类名
     */
    protected static string $class = '';

    /**
     * 忽略的方法
     */
    protected const IGNORE_METHODS = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__serialize',
        '__unserialize',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        '__debugInfo',
    ];

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        $routeCollector = Context::getContainer()->make(\Max\Routing\RouteCollector::class);
        if ($attribute instanceof Controller) {
            self::$router = new Router($attribute->prefix, middlewares: $attribute->middlewares, routeCollector: $routeCollector);
            self::$class  = $class;
        } else if ($attribute instanceof AutoController) {
            $router = new Router($attribute->prefix, patterns: $attribute->patterns, middlewares: $attribute->middlewares, routeCollector: $routeCollector);
            foreach (Reflection::class($class)->getMethods() as $reflectionMethod) {
                $methodName = $reflectionMethod->getName();
                if (!self::isIgnoredMethod($methodName) && $reflectionMethod->isPublic() && !$reflectionMethod->isAbstract()) {
                    $router->request($attribute->prefix . Str::snake($methodName, '-'), [$class, $methodName], $attribute->methods);
                }
            }
        }
    }

    /**
     * @throws NotFoundException
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof MappingInterface && self::$class === $class && !is_null(self::$router)) {
            self::$router->request($attribute->path, [$class, $method], $attribute->methods)
                         ->middlewares($attribute->middlewares)
                         ->domain($attribute->domain);
        }
    }

    /**
     * 是否是忽略的方法
     */
    protected static function isIgnoredMethod(string $method): bool
    {
        return in_array($method, self::IGNORE_METHODS);
    }
}
