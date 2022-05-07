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
     * 添加中间件
     *
     * @param string ...$middlewares
     *
     * @return Router
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
}
