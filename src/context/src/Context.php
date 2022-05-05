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

namespace Max\Context;

use Swoole\Coroutine;

class Context
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param $key
     *
     * @return mixed
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
     *
     * @return void
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
     *
     * @return void
     */
    public static function delete($key = null): void
    {
        if (($cid = self::getCid()) > 0) {
            if (!is_null($key)) {
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
     *
     * @return bool
     */
    public static function has($key): bool
    {
        if (($cid = self::getCid()) > 0) {
            return isset(self::for($cid)[$key]);
        }
        return isset(self::$container[$key]);
    }

    /**
     * @param int|null $cid
     *
     * @return mixed
     */
    public static function for(?int $cid = null): mixed
    {
        return Coroutine::getContext($cid);
    }

    /**
     * @return mixed
     */
    protected static function getCid(): mixed
    {
        return Coroutine::getCid();
    }
}
