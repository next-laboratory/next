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

use Closure;
use Max\Utils\Traits\AutoFillProperties;
use function array_merge;
use function array_unique;
use function sprintf;

class Router
{
    use AutoFillProperties;

    /**
     * 分组中间件
     *
     * @var array
     */
    protected array $middlewares = [];

    /**
     * 前缀
     *
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var string
     */
    protected string $namespace = '';

    /**
     * 域名
     *
     * @var string
     */
    protected string $domain = '';

    /**
     * @var array
     */
    protected array $patterns = [];

    /**
     * @var RouteCollector
     */
    protected RouteCollector $routeCollector;

    /**
     * @param array               $options
     * @param RouteCollector|null $routeCollector
     */
    public function __construct(array $options = [], ?RouteCollector $routeCollector = null)
    {
        $this->fillProperties($options);
        $this->routeCollector = $routeCollector ?? new RouteCollector();
    }

    /**
     * For example: $router->any('/', [IndexController::class, 'index'])
     *
     * @param string               $path   The request path.
     * @param array|Closure|string $action The handling method.
     */
    public function any(string $path, array|Closure|string $action): Route
    {
        return $this->request($path, $action, ['GET', 'HEAD', 'POST', 'OPTIONS', 'PUT', 'PATCH', 'DELETE']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function patch(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['PATCH']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['PUT']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['DELETE']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['POST']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['GET', 'HEAD']);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function options(string $uri, $action): Route
    {
        return $this->request($uri, $action, ['OPTIONS']);
    }

    /**
     * @param string               $path
     * @param array|Closure|string $action
     * @param array                $methods
     *
     * @return Route
     */
    public function request(string $path, array|Closure|string $action, array $methods = ['GET', 'HEAD', 'POST']): Route
    {
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [$this->namespace . '\\' . $controller, $action];
        }
        if (is_string($action)) {
            $action = $this->namespace . '\\' . $action;
        }
        $route = new Route(
            $methods,
            $this->prefix . $path,
            $action,
            $this,
            $this->domain,
        );
        $this->routeCollector->add($route);

        return $route;
    }

    /**
     * 分组路由
     *
     * @param Closure $group
     */
    public function group(Closure $group): void
    {
        $group($this);
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * 添加中间件
     *
     * @param string ...$middlewares
     *
     * @return Router
     * @deprecated
     */
    public function middleware(string ...$middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return Router
     * @deprecated
     */
    public function domain(string $domain): Router
    {
        $new         = clone $this;
        $new->domain = $domain;

        return $new;
    }

    /**
     * @param array $patterns
     *
     * @return Router
     * @deprecated
     */
    public function patterns(array $patterns): Router
    {
        $new           = clone $this;
        $new->patterns = array_merge($this->patterns, $patterns);

        return $new;
    }

    /**
     * 设置前缀
     *
     * @param string $prefix
     *
     * @return $this
     * @deprecated
     */
    public function prefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    /**
     * @param string $namespace
     *
     * @return Router
     * @deprecated
     */
    public function namespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = sprintf('%s\\%s', $this->namespace, $namespace);

        return $new;
    }

    /**
     * @return RouteCollector
     */
    public function getRouteCollector(): RouteCollector
    {
        return $this->routeCollector;
    }

    public function withPrefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    public function withNamespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = sprintf('%s\\%s', $this->namespace, trim($namespace, '\\'));

        return $new;
    }

    public function withPattern(string $parameter, string $pattern): Router
    {
        $new                       = clone $this;
        $new->patterns[$parameter] = $pattern;
        return $new;
    }

    public function withPatterns(array $patterns): Router
    {
        $new           = clone $this;
        $new->patterns = array_merge($this->patterns, $patterns);

        return $new;
    }

    public function withMiddleware(string $middleware): Router
    {
        $new = clone $this;
        if (!in_array($middleware, $new->middlewares)) {
            $new->middlewares[] = $middleware;
        }
        return $new;
    }

    public function withDomain(string $domain): Router
    {
        $new         = clone $this;
        $new->domain = $domain;

        return $new;
    }

    public function withMiddlewares(array $middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }
}
