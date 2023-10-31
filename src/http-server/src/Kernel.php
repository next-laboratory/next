<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server;

use Next\Http\Server\Contract\HttpKernelInterface;
use Next\Http\Server\Contract\RouteDispatcherInterface;
use Next\Routing\Route;
use Next\Routing\RouteCollection;
use Next\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

class Kernel implements HttpKernelInterface
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
    public function handle(ServerRequestInterface $request): ResponseInterface
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
