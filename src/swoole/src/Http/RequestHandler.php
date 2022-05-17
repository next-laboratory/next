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

namespace Max\Swoole\Http;

use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Max\Swoole\Http\Exceptions\InvalidRequestHandlerException;
use Max\Swoole\Http\Exceptions\InvalidResponseBodyException;
use Max\Http\Message\Stream\StringStream;
use Max\Http\Server\RequestHandler as PsrRequestHandler;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Max\Utils\Contracts\Arrayable;
use Max\Utils\Stringable;
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
     * @param ResponseInterface $response
     */
    public function __construct(RouteCollector $routeCollector, protected ResponseInterface $response)
    {
        $this->map($this->router = new Router([], $routeCollector));
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
        return (new PsrRequestHandler())
            ->setContainer(Context::getContainer())
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
        $route = \Max\Context\Context::get(Route::class);
        $action = $route->getAction();
        $params = $route->getParameters();
        if (is_string($action)) {
            $action = explode('@', $action, 2);
        }
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [Context::getContainer()->make($controller), $action];
        }

        return $this->autoResponse(call($action, array_filter($params, fn($value) => !is_null($value))));
    }

    /**
     * @param $response
     *
     * @return ResponseInterface
     */
    protected function autoResponse($response): ResponseInterface
    {
        if (!$response instanceof ResponseInterface) {
            $response = match (true) {
                $response instanceof Arrayable => $this->jsonResponse($response->toArray()),
                $response instanceof Stringable => $this->htmlResponse($response->__toString()),
                is_scalar($response) || is_null($response) => $this->htmlResponse((string)$response),
                default => $this->jsonResponse($response),
            };
        }
        return $response;
    }

    /**
     * @param $data
     *
     * @return mixed|ResponseInterface|ServerRequestInterface
     */
    protected function jsonResponse($data)
    {
        return $this->response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody(new StringStream(json_encode($data)));
    }

    /**
     * @param $data
     *
     * @return mixed|ResponseInterface|ServerRequestInterface
     */
    protected function htmlResponse($data): mixed
    {
        return $this->response
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody(new StringStream($data));
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->router->{$name}(...$arguments);
    }
}
