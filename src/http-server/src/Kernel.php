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

namespace Max\Http\Server;

use Max\Http\Server\Events\OnRequest;
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
    protected array $middlewares = [
        'Max\Http\Server\Middlewares\ExceptionHandleMiddleware',
        'Max\Http\Server\Middlewares\RoutingMiddleware',
    ];

    /**
     * @param RouteCollector            $routeCollector  路由收集器
     * @param ContainerInterface        $container       容器
     * @param ?EventDispatcherInterface $eventDispatcher 事件调度器
     */
    final public function __construct(
        protected RouteCollector $routeCollector,
        protected ContainerInterface $container,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
        $this->map(new Router([], $routeCollector));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function through(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new RequestHandler($this->container, $this->middlewares))->handle($request);
        $this->eventDispatcher?->dispatch(new OnRequest($request, $response));
        return $response;
    }

    /**
     * 路由注册.
     */
    protected function map(Router $router): void
    {
    }
}
