<?php
declare(strict_types=1);

namespace Max\Utils;

use Swoole\Coroutine;
use Max\Utils\Coroutine\Context as CoContext;

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
     */
    public static function put($key, $item)
    {
        $cid = Coroutine::getCid();
        if ($cid > 0) {
            CoContext::put($cid, $key, $item);
        } else {
            self::$pool[$key] = $item;
        }
    }

    /**
     * @param null $key
     */
    public static function delete($key = null)
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
