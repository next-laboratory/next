<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use BadMethodCallException;
use Max\Http\Server\Events\OnRequest;
use Max\Routing\Exceptions\RouteNotFoundException;
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
     * 初始路由对象
     */
    protected Router $router;

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
        $this->map($this->router = new Router(routeCollector: $routeCollector));
    }

    /**
     * 文件外部注册路由.
     */
    public function __call(string $name, array $arguments)
    {
        if (in_array($name, ['get', 'post', 'request', 'any', 'put', 'options', 'delete'])) {
            return $this->router->{$name}(...$arguments);
        }
        throw new BadMethodCallException('Method ' . $name . ' does not exist.');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
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
