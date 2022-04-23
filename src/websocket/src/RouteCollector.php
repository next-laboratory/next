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

use Max\Di\Annotation\Collector\AbstractCollector;
use Max\Di\Contracts\CollectorInterface;
use Max\WebSocket\Annotations\WebSocketHandler;
use Max\WebSocket\Contracts\WebSocketHandlerInterface;

class RouteCollector extends AbstractCollector
{
    protected static array $container = [];

    public static function collectClass(string $class, object $attribute): void
    {
        if (self::isValid($attribute)) {
            /** @var WebSocketHandler $attribute */
            self::$container[$attribute->path] = $class;
        }
    }

    public static function getHandler(string $path)
    {
        if (isset(self::$container[$path])) {
            return \Max\Di\Context::getContainer()->make(self::$container[$path]);
        }
        return null;
    }

    public static function isValid(object $attribute)
    {
        return $attribute instanceof WebSocketHandler;
    }
}
