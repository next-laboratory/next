<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server;

use InvalidArgumentException;
use Next\Http\Server\Contract\RouteDispatcherInterface;
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
        protected RouteDispatcherInterface $routeDispatcher,
        protected array $middlewares = [],
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middlewareClass = array_shift($this->middlewares)) {
            $middleware = $this->container->make($middlewareClass);
            if ($middleware instanceof MiddlewareInterface) {
                return $middleware->process($request, $this);
            }
            throw new InvalidArgumentException(sprintf('The middleware %s should implement Psr\Http\Server\MiddlewareInterface', $middlewareClass));
        }
        return $this->routeDispatcher->dispatch($request);
    }

    /**
     * 添加中间件.
     */
    public function use(string ...$middleware): static
    {
        array_push($this->middlewares, ...$middleware);
        return $this;
    }
}
