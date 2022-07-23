<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing;

use Closure;
use InvalidArgumentException;
use function array_merge;
use function array_unique;
use function sprintf;

class Router
{
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
        protected ?RouteCollector $routeCollector = null
    ) {
        $this->routeCollector ??= new RouteCollector();
    }

    /**
     * Allow almost all methods.
     * For example: $router->any('/', [IndexController@class, 'index']).
     *
     * @param string               $path   the request path
     * @param array|Closure|string $action the handling method
     */
    public function any(string $path, array|Closure|string $action): Route
    {
        return $this->request($path, $action, ['GET', 'HEAD', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * Method PATCH.
     */
    public function patch(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['PATCH']);
    }

    /**
     * Method PUT.
     */
    public function put(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['PUT']);
    }

    /**
     * Method DELETE.
     */
    public function delete(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['DELETE']);
    }

    /**
     * Method POST.
     */
    public function post(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['POST']);
    }

    /**
     * Method GET.
     */
    public function get(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['GET', 'HEAD']);
    }

    /**
     * Allow multi request methods.
     */
    public function request(string $path, array|Closure|string $action, array $methods = ['GET', 'HEAD', 'POST']): Route
    {
        if (is_string($action)) {
            $action = explode('@', $this->formatController($action), 2);
        }
        if ($action instanceof Closure || count($action) === 2) {
            if (is_array($action)) {
                [$controller, $action] = $action;
                $action                = [$this->formatController($controller), $action];
            }
            $route = new Route($methods, $this->prefix . $path, $action, $this->patterns, $this->middlewares);
            $this->routeCollector->add($route);

            return $route;
        }
        throw new InvalidArgumentException('Invalid route action: ' . $path);
    }

    /**
     * 分组路由.
     */
    public function group(Closure $group): void
    {
        $group($this);
    }

    /**
     * 添加中间件.
     */
    public function middleware(string ...$middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }

    /**
     * 变量规则.
     * @deprecated
     */
    public function patterns(array $patterns): Router
    {
        $new           = clone $this;
        $new->patterns = array_merge($this->patterns, $patterns);

        return $new;
    }

    /**
     * 单个变量规则
     */
    public function where(string $name, string $pattern): Router
    {
        $new = clone $this;
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
        $new->namespace = sprintf('%s\\%s', $this->namespace, trim($namespace, '\\'));

        return $new;
    }

    public function getRouteCollector(): RouteCollector
    {
        return $this->routeCollector;
    }

    /**
     * 将命名空间和控制器拼接起来.
     */
    protected function formatController(string $controller): string
    {
        return trim($this->namespace . '\\' . ltrim($controller, '\\'), '\\');
    }
}
