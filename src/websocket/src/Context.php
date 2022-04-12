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

class Context
{
    /**
     * WebSocket context. Use file identifiers to distinguish.
     *
     * @var array
     */
    protected static array $container = [];

    /**
     * @param int    $fd
     * @param string $key
     * @param mixed  $value
     */
    public static function put(int $fd, string $key, mixed $value): void
    {
        self::$container[$fd][$key] = $value;
    }

    /**
     * @param int    $fd
     * @param string $key
     *
     * @return mixed
     */
    public static function get(int $fd, string $key): mixed
    {
        return self::$container[$fd][$key] ?? null;
    }

    /**
     * @param int $fd
     */
    public static function delete(int $fd)
    {
        unset(self::$container[$fd]);
    }
}
