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
use function array_merge;
use function array_unique;
use function property_exists;
use function sprintf;

class Router
{
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
        if ([] !== $options) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
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
     * 将命名空间和控制器拼接起来
     */
    protected function longNameController(string $controller): string
    {
        return trim($this->namespace . '\\' . ltrim($controller, '\\'), '\\');
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
            $action = [$this->longNameController($controller), $action];
        }
        if (is_string($action)) {
            $action = $this->longNameController($action);
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
     * 指定域名
     * Example: *.git.com / www.git.com
     */
    public function domain(string $domain): Router
    {
        $new         = clone $this;
        $new->domain = $domain;

        return $new;
    }

    /**
     * 变量规则
     */
    public function patterns(array $patterns): Router
    {
        $new           = clone $this;
        $new->patterns = array_merge($this->patterns, $patterns);

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
     * 命名空间
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
}
