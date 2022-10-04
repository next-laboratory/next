<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing;

use Max\Http\Message\Contract\StatusCodeInterface;
use Max\Routing\Exception\MethodNotAllowedException;
use Max\Routing\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function preg_match;

class RouteCollection
{
    /**
     * 未分组的全部路由.
     *
     * @var array<string, Route[]>
     */
    protected array $routes = [];

    /**
     * 添加一个路由.
     */
    public function addRoute(Route $route): Route
    {
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }
        return $route;
    }

    /**
     * 全部.
     *
     * @return array<string, Route[]>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * 使用ServerRequestInterface对象解析路由.
     *
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     */
    public function resolveRequest(ServerRequestInterface $request): Route
    {
        $path   = '/' . trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        return $this->resolve($method, $path);
    }

    /**
     * 使用请求方法和请求路径解析路由.
     */
    public function resolve(string $method, string $path): Route
    {
        $routes = $this->routes[$method] ?? throw new MethodNotAllowedException('Method not allowed: ' . $method, StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
        foreach ($routes as $route) {
            if (($compiledPath = $route->getCompiledPath()) && preg_match($compiledPath, $path, $match)) {
                $resolvedRoute = clone $route;
                if (!empty($match)) {
                    foreach ($route->getParameters() as $key => $value) {
                        if (array_key_exists($key, $match)) {
                            $resolvedRoute->setParameter($key, $match[$key]);
                        }
                    }
                }
                return $resolvedRoute;
            }
        }

        throw new RouteNotFoundException('Not Found', StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
