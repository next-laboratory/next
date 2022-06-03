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

namespace Max\Routing;

use Max\Routing\Exceptions\MethodNotAllowedException;
use Max\Routing\Exceptions\RouteNotFoundException;
use Max\Http\Exceptions\HttpException;
use Psr\Http\Message\ServerRequestInterface;
use function array_key_exists;
use function is_null;
use function preg_match;

class RouteCollector
{
    /**
     * 未分组的全部路由
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * 添加一个路由
     *
     * @param Route $route
     *
     * @return void
     */
    public function add(Route $route): void
    {
        $domain = $route->getCompiledDomain();
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][$domain][] = $route;
        }
    }

    /**
     * 全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Route
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     * @throws HttpException
     */
    public function resolve(ServerRequestInterface $request): Route
    {
        $path   = '/' . trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        $map    = $this->routes[$method] ?? throw new MethodNotAllowedException('Method not allowed: ' . $method, 405);
        $routes = $map[''] ?? [];
        foreach ($map as $domain => $item) {
            if ('' === $domain) {
                continue;
            }
            if (preg_match($domain, $request->getUri()->getHost())) {
                $routes = array_merge($item, $routes);
            }
        }

        $resolvedRoute = null;
        /* @var Route $route */
        foreach ($routes as $route) {
            // 相等匹配
            if ($route->getPath() === $path) {
                $resolvedRoute = clone $route;
            } else {
                // 正则匹配
                $regexp = $route->getRegexp();
                if (!is_null($regexp) && preg_match($regexp, $path, $match)) {
                    $resolvedRoute = clone $route;
                    if (!empty($match)) {
                        foreach ($route->getParameters() as $key => $value) {
                            if (array_key_exists($key, $match)) {
                                $resolvedRoute->setParameter($key, trim($match[$key], '/'));
                            }
                        }
                    }
                }
            }

            if (!is_null($resolvedRoute)) {
                return $resolvedRoute;
            }
        }
        throw new RouteNotFoundException('Not Found', 404);
    }
}
