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

use Max\Context\Coroutine\Context as CoContext;
use Swoole\Coroutine;

class Context
{
    /**
     * @var array
     */
    protected static array $pool = [];

    /**
     * @param $key
     *
     * @return mixed
     */
    public static function get($key): mixed
    {
        $cid = Coroutine::getCid();
        if ($cid < 0) {
            return self::$pool[$key] ?? null;
        }
        return CoContext::get($cid, $key);
    }

    /**
     * @param $key
     * @param $item
     *
     * @return void
     */
    public static function put($key, $item): void
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            CoContext::put($cid, $key, $item);
        } else {
            self::$pool[$key] = $item;
        }
    }

    /**
     * @param $key
     *
     * @return void
     */
    public static function delete($key = null): void
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            CoContext::delete($cid, $key);
        } else {
            if ($key) {
                unset(self::$pool[$key]);
            } else {
                self::$pool = [];
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
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            return CoContext::has($cid, $key);
        }
        return isset(self::$pool[$key]);
    }
}
