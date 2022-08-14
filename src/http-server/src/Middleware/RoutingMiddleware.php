<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\Middleware;

use Max\Routing\Exception\MethodNotAllowedException;
use Max\Routing\Exception\RouteNotFoundException;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected RouteCollector $routeCollector
    ) {
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->routeCollector->resolveRequest($request);
        return $handler->use($route->getMiddlewares())->handle($request->withAttribute(Route::class, $route));
    }
}
