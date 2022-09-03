<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Http\Server\Contract\RouteResolverInterface;
use Max\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use RuntimeException;

class RouteResolver implements RouteResolverInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(ServerRequestInterface $request): ResponseInterface
    {
        if ($route = $request->getAttribute(Route::class)) {
            $parameters            = $route->getParameters();
            $parameters['request'] = $request;
            return call($route->getAction(), $parameters);
        }
        throw new RuntimeException('No route found in the request context', 404);
    }
}
