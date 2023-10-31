<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server\Middleware;

use Next\Routing\Exception\MethodNotAllowedException;
use Next\Routing\Exception\RouteNotFoundException;
use Next\Routing\Route;
use Next\Routing\UrlMatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected UrlMatcher $urlMatcher,
    ) {
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route   = $this->urlMatcher->matchRequest($request);
        $request = $request->withAttribute(Route::class, $route);

        return $handler->use(...$route->getMiddlewares())->handle($request);
    }
}
