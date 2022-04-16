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
use Max\Http\Server\RequestHandler as PsrRequestHandler;
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
    public function __construct(RouteCollector $routeCollector)
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
            ->setRequestHandler($container->make(Dispatcher::class))
            ->setMiddlewares($this->middlewares)
            ->handle($request);
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
