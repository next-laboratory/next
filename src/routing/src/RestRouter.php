<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing;

class RestRouter
{
    /**
     * Rest路由规则.
     *
     * @var array|array[]
     */
    protected static array $maps = [
        'index'  => [['GET', 'HEAD'], '/%s'],
        'show'   => [['GET', 'HEAD'], '/%s/{id}'],
        'store'  => [['POST'], '/%s'],
        'update' => [['PUT', 'PATCH'], '/%s/{id}'],
        'delete' => [['DELETE'], '/%s/{id}'],
    ];

    protected Route $index;

    protected Route $show;

    protected Route $store;

    protected Route $update;

    protected Route $delete;

    public function __construct(
        protected RouteCollector $routeCollector,
        protected string $uri,
        protected string $controller,
        protected array $middlewares = [],
        protected array $patterns = [],
    ) {
        foreach (static::$maps as $action => $map) {
            [$methods, $path] = $map;
            $this->routeCollector->addRoute($this->{$action} = new Route(
                $methods,
                sprintf($path, $uri),
                [$this->controller, $action],
                $this->patterns,
                $this->middlewares,
            ));
        }
    }

    public function getIndex(): Route
    {
        return $this->index;
    }

    public function getShow(): Route
    {
        return $this->show;
    }

    public function getStore(): Route
    {
        return $this->store;
    }

    public function getUpdate(): Route
    {
        return $this->update;
    }

    public function getDelete(): Route
    {
        return $this->delete;
    }
}
