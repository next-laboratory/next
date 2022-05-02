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

namespace Max\Http;

use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Max\Http\Exceptions\InvalidRequestHandlerException;
use Max\Http\Exceptions\InvalidResponseBodyException;
use Max\Http\Server\RequestHandler as PsrRequestHandler;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Max\Utils\Contracts\Arrayable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * 全局中间件
     *
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var Router
     */
    protected Router $router;

    /**
     * @param RouteCollector $routeCollector
     */
    public function __construct(RouteCollector $routeCollector, protected ResponseInterface $response)
    {
        $this->map($this->router = new Router([], $routeCollector));
        $routeCollector->compile();
    }

    /**
     * @param Router $router
     */
    protected function map(Router $router)
    {
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws InvalidRequestHandlerException
     * @throws ContainerExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = Context::getContainer();
        return (new PsrRequestHandler())
            ->setContainer($container)
            ->setRequestHandler(\Closure::fromCallable([$this, 'handleRequest']))
            ->setMiddlewares($this->middlewares)
            ->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidResponseBodyException
     * @throws ReflectionException
     */
    protected function handleRequest(ServerRequestInterface $request)
    {
        /** @var Route $route */
        $route  = $request->route();
        $action = $route->getAction();
        $params = $route->getParameters();
        if (is_string($action)) {
            $action = explode('@', $action, 2);
        }
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [make($controller), $action];
        }

        return $this->autoResponse(call($action, array_filter($params, fn($value) => !is_null($value))));
    }

    /**
     * @param $response
     *
     * @return ResponseInterface
     * @throws InvalidResponseBodyException
     */
    protected function autoResponse($response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        $response = match (true) {
            $response instanceof Arrayable => $this->response->json($response->toArray()),
            $response instanceof Stringable => $this->response->html($response->__toString()),
            is_scalar($response) || is_null($response) => $this->response->html((string)$response),
            default => $this->response->json($response),
        };
        $this->response->setPsr7($response);

        return $this->response;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->router->{$name}(...$arguments);
    }
}
