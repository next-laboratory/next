<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Routing;

use Next\Http\Message\Contract\StatusCodeInterface;
use Next\Routing\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class UrlMatcher
{
    public function __construct(
        protected RouteCollection $routeCollection
    ) {}

    /**
     * 使用Psr Request匹配路由.
     */
    public function matchRequest(ServerRequestInterface $request): Route
    {
        $path   = '/' . trim($request->getUri()->getPath(), '/');
        $method = $request->getMethod();
        return $this->match($method, $path);
    }

    /**
     * 使用请求方法和请求path来匹配路由.
     */
    public function match(string $method, string $path): Route
    {
        foreach ($this->routeCollection->list($method) as $route) {
            if (($compiledPath = $route->getCompiledPath()) && \preg_match($compiledPath, $path, $match)) {
                $matchedRoute = clone $route;
                if (! empty($match)) {
                    foreach ($route->getParameters() as $key => $value) {
                        if (\array_key_exists($key, $match)) {
                            $matchedRoute->setParameter($key, $match[$key]);
                        }
                    }
                }
                return $matchedRoute;
            }
        }

        throw new RouteNotFoundException('Not Found', StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
