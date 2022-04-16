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

namespace Max\Http\Annotations;

use Attribute;
use Max\Di\Context;
use Max\Di\Contracts\ClassAttribute;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller implements ClassAttribute
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @param string $prefix 前缀
     * @param array $middlewares 中间件
     */
    public function __construct(protected string $prefix = '', protected array $middlewares = [])
    {
        $this->container = Context::getContainer();
        if (!$this->container->has(RouteCollector::class)) {
            $this->container->set(RouteCollector::class, new RouteCollector());
        }
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ReflectionClass $reflectionClass)
    {
        $this->container->set(Router::class, new Router([
            'prefix' => $this->prefix,
            'middlewares' => $this->middlewares,
        ], $this->container->get(RouteCollector::class)));
    }
}
