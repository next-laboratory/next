<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Context;

use Swoole\Coroutine;

class Context
{
    protected static array $container = [];

    /**
     * @param $key
     */
    public static function get($key): mixed
    {
        if (($cid = self::getCid()) < 0) {
            return self::$container[$key] ?? null;
        }
        return self::for($cid)[$key] ?? null;
    }

    /**
     * @param $key
     * @param $item
     */
    public static function put($key, $item): void
    {
        if (($cid = self::getCid()) > 0) {
            self::for($cid)[$key] = $item;
        } else {
            self::$container[$key] = $item;
        }
    }

    /**
     * @param $key
     */
    public static function delete($key = null): void
    {
        if (($cid = self::getCid()) > 0) {
            if (! is_null($key)) {
                unset(self::for($cid)[$key]);
            }
        } else {
            if ($key) {
                unset(self::$container[$key]);
            } else {
                self::$container = [];
            }
        }
    }

    /**
     * @param $key
     */
    public static function has($key): bool
    {
        if (($cid = self::getCid()) > 0) {
            return isset(self::for($cid)[$key]);
        }
        return isset(self::$container[$key]);
    }

    public static function for(?int $cid = null): mixed
    {
        return Coroutine::getContext($cid);
    }

    protected static function getCid(): mixed
    {
        if (class_exists('Swoole\Coroutine')) {
            return Coroutine::getCid();
        }
        return -1;
    }
}
