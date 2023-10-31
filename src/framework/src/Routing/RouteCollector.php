<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing;

use Next\Aop\Collector\AbstractCollector;
use Next\Di\Context;
use Next\Di\Exception\NotFoundException;
use Next\Routing\Attribute\Controller;
use Next\Routing\Attribute\RequestMapping;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RouteCollector extends AbstractCollector
{
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
        if ($attribute instanceof Controller) {
            $routeCollection = Context::getContainer()->make(\Next\Routing\RouteCollection::class);
            $router          = new Router($attribute->prefix, $attribute->patterns, middlewares: $attribute->middlewares, routeCollection: $routeCollection);
            self::$router    = $router;
            self::$class     = $class;
        }
    }

    /**
     * @throws NotFoundException
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof RequestMapping && self::$class === $class && !is_null(self::$router)) {
            self::$router->request($attribute->path, [$class, $method], $attribute->methods)->middleware(...$attribute->middlewares);
        }
    }
}
