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

namespace Max\Http\Server\Middlewares;

use Max\Routing\Exceptions\MethodNotAllowedException;
use Max\Routing\Exceptions\RouteNotFoundException;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(protected RouteCollector $routeCollector)
    {
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->routeCollector->resolve($request);
        $handler->unshiftMiddlewares($route->getMiddlewares());
        return $handler->handle($request->withAttribute(Route::class, $route));
    }
}
