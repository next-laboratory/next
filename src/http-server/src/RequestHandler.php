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

use Max\Http\Server\Exceptions\InvalidMiddlewareException;
use Max\Routing\Exceptions\RouteNotFoundException;
use Max\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected array              $middlewares = []
    )
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middlewares === []) {
            return $this->handleRequest($request);
        }
        return $this->handleMiddleware(array_shift($this->middlewares), $request);
    }

    /**
     * 向尾部追加中间件.
     */
    public function appendMiddlewares(array $middlewares): void
    {
        array_push($this->middlewares, ...$middlewares);
    }

    /**
     * 向当前中间件后插入中间件.
     */
    public function prependMiddlewares(array $middlewares): void
    {
        array_unshift($this->middlewares, ...$middlewares);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException|RouteNotFoundException
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Route $route */
        if ($route = $request->getAttribute(Route::class)) {
            $action = $route->getAction();
            if (is_string($action)) {
                $action = explode('@', $action, 2);
            }
            $parameters            = $route->getParameters();
            $parameters['request'] = $request;
            return $this->container->call($action, $parameters);
        }
        throw new RouteNotFoundException('No route was matched', 404);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function handleMiddleware(string $middleware, ServerRequestInterface $request): ResponseInterface
    {
        $handler = is_null($this->container) ? new $middleware() : $this->container->make($middleware);

        if ($handler instanceof MiddlewareInterface) {
            return $handler->process($request, $this);
        }

        throw new InvalidMiddlewareException(sprintf('Middleware `%s must be an instance of Psr\Http\Server\MiddlewareInterface.', $middleware));
    }
}
