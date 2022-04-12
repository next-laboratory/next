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

namespace Max\Http\Middlewares;

use Max\Di\Annotations\Inject;
use Max\Di\Exceptions\NotFoundException;
use Max\Http\Exceptions\HttpException;
use Max\Http\Exceptions\InvalidRequestHandlerException;
use Max\Routing\Exceptions\MethodNotAllowedException;
use Max\Routing\Exceptions\RouteNotFoundException;
use Max\Routing\RouteCollector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

class RoutingMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected RouteCollector $routeCollector;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws HttpException
     * @throws InvalidRequestHandlerException
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws RouteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->routeCollector->resolve($request);
        $request->route($route);
        $handler->unshift($route->getMiddlewares());
        return $handler->handle($request);
    }
}
