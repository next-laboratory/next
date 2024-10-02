<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Swoole;

use Swoole\Coroutine;
use Swoole\Coroutine\Context as SwooleContext;

class Context
{
    protected static array $container = [];

    public static function get(string $key): mixed
    {
        if (($cid = self::getCid()) < 0) {
            return self::$container[$key] ?? null;
        }
        return self::for($cid)[$key] ?? null;
    }

    public static function put(string $key, mixed $item): void
    {
        if (($cid = self::getCid()) > 0) {
            self::for($cid)[$key] = $item;
        } else {
            self::$container[$key] = $item;
        }
    }

    public static function delete(string $key = ''): void
    {
        if (($cid = self::getCid()) > 0) {
            if (!empty($key)) {
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

    public static function has(string $key): bool
    {
        if (($cid = self::getCid()) > 0) {
            return isset(self::for($cid)[$key]);
        }
        return isset(self::$container[$key]);
    }

    public static function for(?int $cid = null): ?SwooleContext
    {
        return Coroutine::getContext($cid);
    }

    protected static function getCid(): int
    {
        if (class_exists('Swoole\Coroutine')) {
            return Coroutine::getCid();
        }
        return -1;
    }

    public static function inCoroutine(): bool
    {
        return self::getCid() >= 0;
    }
}
