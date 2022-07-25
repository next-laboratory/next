<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing;

use Max\Routing\Exceptions\MethodNotAllowedException;
use Max\Routing\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function preg_match;

class RouteCollector
{
    /**
     * 未分组的全部路由.
     */
    protected array $routes = [];

    /**
     * 添加一个路由.
     */
    public function add(Route $route): Route
    {
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }
        return $route;
    }

    /**
     * 全部.
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function resolveRequest(ServerRequestInterface $request): Route
    {
        $path   = '/' . trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        return $this->resolve($method, $path);
    }

    public function resolve(string $method, string $path)
    {
        $routes = $this->routes[$method] ?? throw new MethodNotAllowedException('Method not allowed: ' . $method, 405);
        foreach ($routes as $route) {
            if (($compiledPath = $route->getCompiledPath()) && preg_match($compiledPath, $path, $match)) {
                $resolvedRoute = clone $route;
                if (! empty($match)) {
                    foreach ($route->getParameters() as $key => $value) {
                        if (array_key_exists($key, $match)) {
                            $resolvedRoute->setParameter($key, $match[$key]);
                        }
                    }
                }
                return $resolvedRoute;
            }
        }

        throw new RouteNotFoundException('Not Found', 404);
    }
}
