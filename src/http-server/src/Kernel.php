<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Http\Server\Contract\RouteDispatcherInterface;
use Max\Routing\Route;
use Max\Routing\RouteCollection;
use Max\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

class Kernel
{
    /**
     * 全局中间件.
     */
    protected array $middlewares = [];

    /**
     * @param ContainerInterface       $container       容器
     * @param RouteCollection          $routeCollection 路由收集器
     * @param RouteDispatcherInterface $routeDispatcher
     */
    final public function __construct(
        protected ContainerInterface $container,
        protected RouteCollection $routeCollection,
        protected RouteDispatcherInterface $routeDispatcher,
    ) {
        $this->map(new Router(routeCollection: $this->routeCollection));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    final public function through(ServerRequestInterface $request): ResponseInterface
    {
        return (new RequestHandler($this->container, $this->routeDispatcher, $this->middlewares))->handle($request);
    }

    /**
     * 添加中间件.
     */
    final public function use(string ...$middleware): static
    {
        array_push($this->middlewares, ...$middleware);
        return $this;
    }

    /**
     * @return array<string,Route[]>
     */
    final public function getAllRoutes(): array
    {
        return $this->routeCollection->all();
    }

    /**
     * 路由注册.
     */
    protected function map(Router $router): void
    {
    }
}
