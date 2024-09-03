<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing;

class Router
{
    protected const ALL_METHODS = ['GET', 'HEAD', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE'];

    /**
     * @param string $prefix      url前缀
     * @param array  $patterns    参数规则
     * @param array  $middlewares 中间件
     * @param string $namespace   命名空间
     */
    public function __construct(
        protected string $prefix = '',
        protected array $patterns = [],
        protected string $namespace = '',
        protected array $middlewares = [],
        protected ?RouteCollection $routeCollection = null
    ) {
        $this->routeCollection ??= new RouteCollection();
    }

    /**
     * Allow almost all methods.
     * For example: $router->any('/', [IndexController@class, 'index']).
     *
     * @param string                $path   the request path
     * @param array|\Closure|string $action the handling method
     */
    public function any(string $path, array|\Closure|string $action): Route
    {
        return $this->request($path, $action, self::ALL_METHODS);
    }

    /**
     * Method PATCH.
     */
    public function patch(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['PATCH']);
    }

    /**
     * Method OPTIONS.
     */
    public function options(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['OPTIONS']);
    }

    /**
     * Method PUT.
     */
    public function put(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['PUT']);
    }

    /**
     * Method DELETE.
     */
    public function delete(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['DELETE']);
    }

    /**
     * Method POST.
     */
    public function post(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['POST']);
    }

    /**
     * Method GET.
     */
    public function get(string $uri, array|\Closure|string $action): Route
    {
        return $this->request($uri, $action, ['GET', 'HEAD']);
    }

    /**
     * Allow multi request methods.
     */
    public function request(string $path, array|\Closure|string $action, array $methods = ['GET', 'HEAD', 'POST']): Route
    {
        if (is_string($action)) {
            $action = str_contains($action, '@')
                ? explode('@', $this->formatController($action), 2)
                : [$this->formatController($action), '__invoke'];
        }
        if ($action instanceof \Closure || count($action) === 2) {
            if (is_array($action)) {
                [$controller, $action] = $action;
                $action                = [$this->formatController($controller), $action];
            }
            return $this->routeCollection->add(new Route($methods, $this->prefix . $path, $action, $this->patterns, $this->middlewares));
        }
        throw new \InvalidArgumentException('Invalid route action: ' . $path);
    }

    /**
     * 分组路由.
     */
    public function group(\Closure $group): void
    {
        $group($this);
    }

    /**
     * 添加中间件.
     */
    public function middleware(string ...$middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = \array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }

    /**
     * 单个变量规则.
     */
    public function where(string $name, string $pattern): Router
    {
        $new                  = clone $this;
        $new->patterns[$name] = $pattern;

        return $new;
    }

    /**
     * 前缀
     */
    public function prefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    /**
     * 命名空间.
     */
    public function namespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = \sprintf('%s\%s', $this->namespace, trim($namespace, '\\'));

        return $new;
    }

    /**
     * 路由收集器.
     */
    public function getRouteCollection(): RouteCollection
    {
        return $this->routeCollection;
    }

    /**
     * 将命名空间和控制器拼接起来.
     */
    protected function formatController(string $controller): string
    {
        return trim($this->namespace . '\\' . ltrim($controller, '\\'), '\\');
    }
}
