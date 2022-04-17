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

namespace Max\WebSocket;

use Max\WebSocket\Contracts\WebSocketHandlerInterface;

class RouteCollector
{
    /**
     * @var array
     */
    protected static array $routes = [];

    /**
     * @param string $path
     * @param WebSocketHandlerInterface $webSocketHandler
     * @return void
     */
    public static function addRoute(string $path, WebSocketHandlerInterface $webSocketHandler)
    {
        self::$routes[$path] = $webSocketHandler;
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public static function getRoute(string $path): mixed
    {
        return self::$routes[$path] ?? null;
    }
}
