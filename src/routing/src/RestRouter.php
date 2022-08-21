<?php

namespace Max\Routing;

class RestRouter
{
    /**
     * Rest路由规则
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

    /**
     * @var Route
     */
    protected Route $index;
    /**
     * @var Route
     */
    protected Route $show;
    /**
     * @var Route
     */
    protected Route $store;
    /**
     * @var Route
     */
    protected Route $update;
    /**
     * @var Route
     */
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

    /**
     * @return Route
     */
    public function getIndex(): Route
    {
        return $this->index;
    }

    /**
     * @return Route
     */
    public function getShow(): Route
    {
        return $this->show;
    }

    /**
     * @return Route
     */
    public function getStore(): Route
    {
        return $this->store;
    }

    /**
     * @return Route
     */
    public function getUpdate(): Route
    {
        return $this->update;
    }

    /**
     * @return Route
     */
    public function getDelete(): Route
    {
        return $this->delete;
    }
}
