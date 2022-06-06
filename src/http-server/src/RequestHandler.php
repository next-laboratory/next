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

use Max\HttpMessage\Response;
use Max\HttpServer\Exceptions\InvalidMiddlewareException;
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
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ([] === $this->middlewares) {
            return $this->handleRequest($request);
        }
        return $this->handleMiddleware(array_shift($this->middlewares), $request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Route $route */
        $route    = $request->getAttribute(Route::class);
        $params   = $route->getParameters();
        $params[] = Context::create($request, new Response());
        $action   = $route->getAction();
        if (is_string($action)) {
            $action = explode('@', $action, 2);
        }
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [$this->container->make($controller), $action];
        }

        return $this->container->call($action, $params);
    }

    /**
     * @param string                 $middleware
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function handleMiddleware(string $middleware, ServerRequestInterface $request): ResponseInterface
    {
        $handler = is_null($this->container) ? new $middleware() : $this->container->make($middleware);

        if ($handler instanceof MiddlewareInterface) {
            return $handler->process($request, $this);
        }

        throw new InvalidMiddlewareException(sprintf('Middleware `%s must implement the `Psr\Http\Server\MiddlewareInterface` interface.', $middleware));
    }

    /**
     * 向尾部追加中间件
     *
     * @param array $middlewares
     *
     * @return void
     */
    public function pushMiddlewares(array $middlewares): void
    {
        array_push($this->middlewares, ...$middlewares);
    }

    /**
     * 从头部插入中间件
     *
     * @param array $middlewares
     *
     * @return void
     */
    public function unshiftMiddlewares(array $middlewares): void
    {
        array_unshift($this->middlewares, ...$middlewares);
    }
}
