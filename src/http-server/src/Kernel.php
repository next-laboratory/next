<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Http\Server\Contract\RouteResolverInterface;
use Max\Http\Server\Event\OnRequest;
use Max\Routing\Exception\RouteNotFoundException;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
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
     * @param RouteCollector            $routeCollector  路由收集器
     * @param ContainerInterface        $container       容器
     * @param ?EventDispatcherInterface $eventDispatcher 事件调度器
     */
    final public function __construct(
        protected ContainerInterface $container,
        protected RouteCollector $routeCollector,
        protected RouteResolverInterface $routeResolver,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->map(new Router(routeCollector: $routeCollector));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
     */
    public function through(ServerRequestInterface $request): ResponseInterface
    {
        $event           = new OnRequest($request);
        $response        = (new RequestHandler($this->container, $this->routeResolver, $this->middlewares))->handle($request);
        $event->response = $response;
        $this->eventDispatcher?->dispatch($event);
        return $response;
    }

    /**
     * 添加中间件.
     */
    public function use(string ...$middleware): static
    {
        array_push($this->middlewares, ...$middleware);
        return $this;
    }

    /**
     * @return array<string,Route[]>
     */
    public function getAllRoutes(): array
    {
        return $this->routeCollector->all();
    }

    /**
     * 路由注册.
     */
    protected function map(Router $router): void
    {
    }
}
