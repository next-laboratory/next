<?php
declare(strict_types=1);

namespace Max\Utils\Coroutine;

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
     * @param $cid
     * @param $key
     *
     * @return mixed
     */
    public static function get($cid, $key): mixed
    {
        return self::$pool[$cid][$key] ?? null;
    }

    /**
     * @param $cid
     * @param $key
     * @param $item
     */
    public static function put($cid, $key, $item)
    {
        self::$pool[$cid][$key] = $item;
    }

    /**
     * @param null $key
     */
    public static function delete($cid, $key = null)
    {
        if ($key) {
            unset(self::$pool[$cid][$key]);
        } else {
            unset(self::$pool[$cid]);
        }
    }

    /**
     * @param $cid
     * @param $key
     *
     * @return bool
     */
    public static function has($cid, $key): bool
    {
        return isset(static::$pool[$cid][$key]);
    }
}
