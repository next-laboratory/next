<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Routing\Exception\RouteNotFoundException;
use Max\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use RuntimeException;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected array $middlewares = []
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = array_shift($this->middlewares)) {
            return $this->handleMiddleware($this->container->make($middleware), $request);
        }
        return $this->handleRequest($request);
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
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = $request->getAttribute(Route::class)) {
            $parameters            = $route->getParameters();
            $parameters['request'] = $request;
            return $this->container->call($route->getAction(), $parameters);
        }
        throw new RuntimeException('No route found in the request context', 404);
    }

    /**
     * 处理中间件.
     */
    protected function handleMiddleware(MiddlewareInterface $middleware, ServerRequestInterface $request): ResponseInterface
    {
        return $middleware->process($request, $this);
    }
}
