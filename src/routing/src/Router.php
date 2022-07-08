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
     */
    protected array $middlewares = [];

    /**
     * 前缀
     */
    protected string $prefix = '';

    /**
     * 命名空间
     */
    protected string $namespace = '';

    /**
     * 域名
     */
    protected string $domain = '';

    /**
     * 参数规则
     */
    protected array $patterns = [];

    /**
     * 路由收集器
     */
    protected RouteCollector $routeCollector;

    /**
     * @param array $options 初始配置
     */
    public function __construct(array $options = [], ?RouteCollector $routeCollector = null)
    {
        $this->fillProperties($options);
        $this->routeCollector = $routeCollector ?? new RouteCollector();
    }

    /**
     * Allow almost all methods.
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
     * Method OPTIONS.
     */
    public function options(string $uri, string|array|Closure $action): Route
    {
        return $this->request($uri, $action, ['OPTIONS']);
    }

    /**
     * Allow multi request methods.
     */
    public function request(
        string               $path,
        array|Closure|string $action,
        array                $methods = ['GET', 'HEAD', 'POST']
    ): Route
    {
        if (is_array($action)) {
            [$controller, $action] = $action;
            $action = [$this->namespace . '\\' . ltrim($controller, '\\'), $action];
        }
        if (is_string($action)) {
            $action = $this->namespace . '\\' . ltrim($action, '\\');
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
     * @deprecated
     */
    public function domain(string $domain): Router
    {
        $new         = clone $this;
        $new->domain = $domain;

        return $new;
    }

    /**
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
     * @deprecated
     */
    public function prefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    /**
     * @deprecated
     */
    public function namespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = sprintf('%s\\%s', $this->namespace, trim($namespace, '\\'));

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
