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
     * 编译后的路由
     *
     * @var array
     */
    protected array $compiled = [];

    /**
     * 添加一个路由
     *
     * @param Route $route
     *
     * @return void
     */
    public function add(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param string $method
     *
     * @return array
     * @throws MethodNotAllowedException
     */
    public function getByMethod(string $method): array
    {
        if (isset($this->compiled[$method])) {
            return $this->compiled[$method];
        }
        throw new MethodNotAllowedException('Method not allowed: ' . $method, 405);
    }

    /**
     * 全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->compiled;
    }

    /**
     * 编译路由
     */
    public function compile()
    {
        /** @var Route $route */
        foreach ($this->routes as $key => $route) {
            $route->compile();
            foreach ($route->getMethods() as $method) {
                $this->compiled[$method][$route->getCompiledDomain()][] = $route;
            }
            unset($this->routes[$key]);
        }
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
        $path          = '/' . trim($request->getUri()->getPath(), '/');
        $map           = $this->getByMethod($request->getMethod());
        $routes        = $map[''] ?? [];
        $matchedDomain = '';
        foreach ($map as $domain => $item) {
            if ('' === $domain) {
                continue;
            }
            if (preg_match($domain, $request->getUri()->getHost(), $matches)) {
                $matchedDomain = $matches[0];
                $routes        = array_merge($item, $routes);
            }
        }

        $resolvedRoute = null;
        /* @var Route $route */
        foreach ($routes as $route) {
            // 相等匹配
            if ($route->getPath() === $path) {
                $resolvedRoute = clone $route;
            }
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
            if (!is_null($resolvedRoute)) {
                $resolvedRoute->setParameter('matchedDomain', $matchedDomain);
                return $resolvedRoute;
            }
        }
        throw new RouteNotFoundException('Not Found', 404);
    }
}
