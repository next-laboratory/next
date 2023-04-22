<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Routing;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Max\Http\Message\Contract\StatusCodeInterface;
use Max\Routing\Exception\MethodNotAllowedException;

class RouteCollection implements IteratorAggregate, Countable
{
    /**
     * 未分组的全部路由.
     *
     * @var array<string, Route[]>
     */
    protected array $routes = [];

    /**
     * 添加一个路由.
     */
    public function add(Route $route): Route
    {
        foreach ($route->getMethods() as $method) {
            $this->routes[$method][] = $route;
        }
        return $route;
    }

    /**
     * 全部.
     *
     * @return array<string, Route[]>
     */
    public function all(): array
    {
        return $this->routes;
    }

    /**
     * @return Route[]
     */
    public function list(string $method): array
    {
        return $this->routes[$method]
            ?? throw new MethodNotAllowedException('Method not allowed: ' . $method, StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
    }

    public function count(): int
    {
        return \count($this->routes);
    }

    /**
     * @return ArrayIterator<string, Route>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->routes);
    }
}