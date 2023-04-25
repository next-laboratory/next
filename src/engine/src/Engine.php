<?php

namespace Max\Engine;

use App\Exception\MethodNotAllowedException;
use App\Exception\NotFoundException;
use Closure;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Engine
{
    /**
     * @var array<Route>
     */
    protected array $routes = [];
    protected Dispatcher $dispatcher;

    public function match(array $methods, string $pattern, callable $handler): Route
    {
        return $this->routes[] = new Route($methods, $pattern, $handler);
    }

    public function get(string $pattern, callable $handler): Route
    {
        return $this->match(['GET'], $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): Route
    {
        return $this->match(['POST'], $pattern, $handler);
    }

    public function put(string $pattern, callable $handler): Route
    {
        return $this->match(['PUT'], $pattern, $handler);
    }

    public function patch(string $pattern, callable $handler): Route
    {
        return $this->match(['PATCH'], $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): Route
    {
        return $this->match(['DELETE'], $pattern, $handler);
    }

    public function handler(): Closure
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->methods, $route->pattern, $route->handler());
            }
        });

        return function (Context $context) {
            $context->next();
        };
    }

    /**
     * @throws MethodNotAllowedException
     * @throws NotFoundException
     */
    public function resolve($httpMethod, $uri)
    {
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rawurldecode($uri);
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundException(404, 'Not Found');
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new MethodNotAllowedException(405, 'Method Not Allowed', allowMethods: $allowedMethods);
            case Dispatcher::FOUND:
                return $routeInfo;
        }
    }
}