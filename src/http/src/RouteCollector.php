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

namespace Max\Http;

use Max\Aop\Annotation\Collector\AbstractCollector;
use Max\Container\Context;
use Max\Container\Exceptions\NotFoundException;
use Max\Container\ReflectionManager;
use Max\Http\Annotations\AutoController;
use Max\Http\Annotations\Controller;
use Max\Http\Contracts\MappingInterface;
use Max\Routing\Route;
use Max\Routing\Router;
use Max\Utils\Str;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RouteCollector extends AbstractCollector
{
    /**
     * @var Router|null
     */
    protected static ?Router $router = null;

    /**
     * 当前控制器的类名
     *
     * @var string
     */
    protected static string $class = '';

    /**
     * @param string $class
     * @param object $attribute
     *
     * @return void
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof Controller) {
            self::$class  = $class;
            self::$router = new Router([
                'prefix'      => $attribute->prefix,
                'middlewares' => $attribute->middlewares,
            ], Context::getContainer()->make(\Max\Routing\RouteCollector::class));
        }
        if ($attribute instanceof AutoController) {
            $router = new Router([
                'prefix'      => $attribute->prefix,
                'middlewares' => $attribute->middlewares,
            ]);
            foreach (ReflectionManager::reflectClass($class)->getMethods() as $reflectionMethod) {
                if ($reflectionMethod->isPublic() && !$reflectionMethod->isStatic() && !$reflectionMethod->isAbstract()) {
                    $action = $reflectionMethod->getName();
                    /** @var \Max\Routing\RouteCollector $routeCollector */
                    $routeCollector = Context::getContainer()->make(\Max\Routing\RouteCollector::class);
                    $routeCollector->add((new Route(
                        $attribute->methods,
                        $attribute->prefix . Str::snake($action, '-'),
                        [$class, $action],
                        $router,
                    )));
                }
            }
        }
    }

    /**
     * @param string $class
     * @param string $method
     * @param object $attribute
     *
     * @return void
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof MappingInterface && self::$class === $class && !is_null(self::$router)) {
            /** @var \Max\Routing\RouteCollector $routeCollector */
            $routeCollector = Context::getContainer()->make(\Max\Routing\RouteCollector::class);
            $routeCollector->add((new Route(
                $attribute->methods,
                self::$router->getPrefix() . $attribute->path,
                [$class, $method],
                self::$router,
                $attribute->domain,
            ))->middlewares($attribute->middlewares));
        }
    }
}
