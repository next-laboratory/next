<?php

namespace Max\HttpServer;

use Max\Http\Message\ServerRequest;
use Max\HttpServer\Contracts\ExceptionHandlerInterface;
use Max\HttpServer\ResponseEmitter\FPMResponseEmitter;
use Max\HttpServer\ResponseEmitter\SwooleResponseEmitter;
use Max\HttpServer\ResponseEmitter\WorkermanResponseEmitter;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerInterface;
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
    protected array  $middlewares = [];
    protected Router $router;

    final public function __construct(
        protected RouteCollector            $routeCollector,
        protected ContainerInterface        $container,
        protected ExceptionHandlerInterface $exceptionHandler,
    )
    {
        $this->map($this->router = new Router([], $routeCollector));
    }

    protected function map(Router $router): void
    {
    }

    public function handleSwooleRequest(Request $request, Response $response): void
    {
        (new SwooleResponseEmitter())->emit(
            $this->handle(ServerRequest::createFromSwooleRequest($request)), $response
        );
    }

    public function handleWorkermanRequest(TcpConnection $tcpConnection, WorkermanRequest $request): void
    {
        (new WorkermanResponseEmitter())->emit(
            $this->handle(ServerRequest::createFromWorkermanRequest($request)), $tcpConnection
        );
    }

    public function handleFPMRequest(): void
    {
        (new FPMResponseEmitter())->emit($this->handle(ServerRequest::createFromGlobals()));
    }

    final protected function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        try {
            $route          = $this->routeCollector->resolve($serverRequest);
            $middlewares    = array_merge($this->middlewares, $route->getMiddlewares());
            $requestHandler = new RequestHandler($this->container, $route, $middlewares);
            return $requestHandler->handle($serverRequest);
        } catch (\Throwable $throwable) {
            return $this->exceptionHandler->handleException($throwable, $serverRequest);
        }
    }
}
