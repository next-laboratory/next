<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Di\Context;
use Max\Http\Server\Contract\RouteDispatcherInterface;
use Max\Routing\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use RuntimeException;

class RouteDispatcher implements RouteDispatcherInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Route $route */
        if ($route = $request->getAttribute(Route::class)) {
            $parameters            = $route->getParameters();
            $parameters['request'] = $request;
            return Context::getContainer()->call($route->getAction(), $parameters);
        }
        throw new RuntimeException('No route found in the request context', 404);
    }
}
