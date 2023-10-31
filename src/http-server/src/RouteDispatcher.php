<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server;

use Next\Di\Context;
use Next\Http\Server\Contract\RouteDispatcherInterface;
use Next\Routing\Route;
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
