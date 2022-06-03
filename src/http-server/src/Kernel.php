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

namespace Max\HttpServer;

use Max\HttpMessage\ServerRequest;
use Max\HttpServer\Contracts\ExceptionHandlerInterface;
use Max\HttpServer\Events\OnRequest;
use Max\HttpServer\ResponseEmitter\FPMResponseEmitter;
use Max\HttpServer\ResponseEmitter\SwooleResponseEmitter;
use Max\HttpServer\ResponseEmitter\WorkermanResponseEmitter;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;

class Kernel
{
    /**
     * 全局中间件
     */
    protected array $middlewares = [];

    /**
     * @param RouteCollector            $routeCollector   路由收集器
     * @param ContainerInterface        $container        容器
     * @param ExceptionHandlerInterface $exceptionHandler 错误处理实例
     * @param ?EventDispatcherInterface $eventDispatcher  事件调度器
     */
    final public function __construct(
        protected RouteCollector            $routeCollector,
        protected ContainerInterface        $container,
        protected ExceptionHandlerInterface $exceptionHandler,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    )
    {
        $this->map(new Router([], $routeCollector));
    }

    /**
     * 路由注册
     *
     * @param Router $router
     *
     * @return void
     */
    protected function map(Router $router): void
    {
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    public function handleSwooleRequest(Request $request, Response $response): void
    {
        (new SwooleResponseEmitter())->emit(
            $this->handle(ServerRequest::createFromSwooleRequest($request)), $response
        );
    }

    /**
     * @param TcpConnection    $tcpConnection
     * @param WorkermanRequest $request
     *
     * @return void
     */
    public function handleWorkermanRequest(TcpConnection $tcpConnection, WorkermanRequest $request): void
    {
        (new WorkermanResponseEmitter())->emit(
            $this->handle(ServerRequest::createFromWorkermanRequest($request)), $tcpConnection
        );
    }

    /**
     * @return void
     */
    public function handleFPMRequest(): void
    {
        (new FPMResponseEmitter())->emit($this->handle(ServerRequest::createFromGlobals()));
    }

    /**
     * @param ServerRequestInterface $serverRequest
     *
     * @return ResponseInterface
     */
    final protected function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        try {
            $route       = $this->routeCollector->resolve($serverRequest);
            $middlewares = array_merge($this->middlewares, $route->getMiddlewares());
            $response    = (new RequestHandler($this->container, $route, $middlewares))->handle($serverRequest);
        } catch (\Throwable $throwable) {
            $response = $this->exceptionHandler->handleException($throwable, $serverRequest);
        } finally {
            $this->eventDispatcher?->dispatch(new OnRequest($serverRequest, $response));
            return $response;
        }
    }
}
