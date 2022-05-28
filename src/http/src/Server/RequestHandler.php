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

use Closure;
use Max\Di\Exceptions\NotFoundException;
use Max\Http\Exceptions\InvalidRequestHandlerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use function is_array;
use function is_string;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * @var array[]|string[]|MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * @var Closure|RequestHandlerInterface
     */
    protected Closure|RequestHandlerInterface $requestHandler;

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws InvalidRequestHandlerException
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ([] === $this->middlewares) {
            return $this->handleRequest($request);
        }
        return $this->handleMiddleware(array_shift($this->middlewares), $request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidRequestHandlerException
     * @throws ReflectionException
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return match (true) {
            $this->requestHandler instanceof Closure => ($this->requestHandler)($request),
            $this->requestHandler instanceof RequestHandlerInterface => $this->requestHandler->handle($request),
            default => throw new InvalidRequestHandlerException('The RequestHandler must be a closure or object that implements the RequestHandlerInterface.'),
        };
    }

    /**
     * @param object|string|array[]  $middleware
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws InvalidRequestHandlerException
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    protected function handleMiddleware(object|array|string $middleware, ServerRequestInterface $request): ResponseInterface
    {
        $parameters = [];

        /**
         * 数组方式传递中间件，[中间件，参数数组]
         */
        if (is_array($middleware)) {
            [$middleware, $parameters] = $middleware;
        }

        $parameters = is_array($parameters) ? $parameters : [$parameters];

        /**
         * 闭包中间件
         */
        if ($middleware instanceof Closure) {
            return $middleware($request, $this, ...$parameters);
        }

        if (is_string($middleware)) {
            $middleware = is_null($this->container) ? new $middleware(...$parameters) : $this->container->make($middleware, $parameters);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }

        throw new InvalidRequestHandlerException('The middleware must be an array, string, or object that implements the MiddlewareInterface.');
    }

    /**
     * 设置中间件
     *
     * @param array $middlewares
     *
     * @return $this
     */
    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return RequestHandler
     */
    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;

        return $this;
    }

    /**
     * 从头部添加中间件
     *
     * @param $middlewares
     */
    public function unshift($middlewares)
    {
        $middlewares = is_array($middlewares) ? $middlewares : func_get_args();
        array_unshift($this->middlewares, ...$middlewares);
    }

    /**
     * @param Closure|RequestHandlerInterface $requestHandler
     *
     * @return $this
     */
    public function setRequestHandler(Closure|RequestHandlerInterface $requestHandler): static
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }
}
