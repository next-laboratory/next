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
use Max\Routing\Contracts\ControllerInterface;
use Max\Routing\Contracts\MappingInterface;
use Max\Routing\Router;
use Max\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RouteCollector extends AbstractCollector
{
    /**
     * 忽略的方法.
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
     * 当前控制器对应的router.
     */
    protected static ?Router $router = null;

    /**
     * 当前控制器的类名.
     */
    protected static string $class = '';

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof ControllerInterface) {
            $routeCollector = Context::getContainer()->make(\Max\Routing\RouteCollector::class);
            $router         = new Router($attribute->prefix, $attribute->patterns, middlewares: $attribute->middlewares, routeCollector: $routeCollector);
            if ($attribute instanceof Controller) {
                self::$router = $router;
                self::$class  = $class;
            } elseif ($attribute instanceof AutoController) {
                foreach (Reflection::class($class)->getMethods() as $reflectionMethod) {
                    $methodName = $reflectionMethod->getName();
                    if (! self::isIgnoredMethod($methodName) && $reflectionMethod->isPublic() && ! $reflectionMethod->isAbstract()) {
                        $router->request($attribute->prefix . Str::snake($methodName, '-'), [$class, $methodName], $attribute->methods);
                    }
                }
            }
        }
    }

    /**
     * @throws NotFoundException
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof MappingInterface && self::$class === $class && ! is_null(self::$router)) {
            self::$router->request($attribute->path, [$class, $method], $attribute->methods)->middleware(...$attribute->middlewares);
        }
    }

    /**
     * 是否是忽略的方法.
     */
    protected static function isIgnoredMethod(string $method): bool
    {
        return in_array($method, self::IGNORE_METHODS);
    }
}
