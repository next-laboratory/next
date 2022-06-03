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

use Closure;
use Max\Di\Context;
use Max\Http\Exceptions\InvalidRequestHandlerException;
use Max\Http\Server\RequestHandler as PsrRequestHandler;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Routing\Router;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

class RequestHandler implements RequestHandlerInterface
{
    /**
     * 全局中间件
     */
    protected array  $middlewares = [];
    protected Router $router;

    public function __construct(RouteCollector $routeCollector)
    {
        $this->map($this->router = new Router([], $routeCollector));
    }

    protected function map(Router $router)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidRequestHandlerException
     * @throws ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new PsrRequestHandler())
            ->setContainer(Context::getContainer())
            ->setRequestHandler(Closure::fromCallable([$this, 'handleRequest']))
            ->setMiddlewares($this->middlewares)
            ->handle($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Route $route */
        $route  = \Max\Context\Context::get(Route::class);
        $action = $route->getAction();
        $params = $route->getParameters();
        if (is_string($action)) {
            $action = explode('@', $action, 2);
        }
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [Context::getContainer()->make($controller), $action];
        }
        // 控制器方法必须返回Response实例
        return call($action, array_filter($params, fn($value) => !is_null($value)));
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->router->{$name}(...$arguments);
    }
}
