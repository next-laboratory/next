<?php

declare (strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Env;

use ArrayAccess;
use Max\Env\Contracts\LoaderInterface;
use Max\Utils\Arr;
use function strtoupper;

class Env
{
    /**
     * @param LoaderInterface $loader
     */
    public function load(LoaderInterface $loader)
    {
        $this->push($loader->export());
    }

    /**
     * 设置env
     *
     * @param string $env
     * @param null   $value
     *
     * @return Env
     */
    public function set(string $env, $value)
    {
        $_ENV[strtoupper($env)] = $value;

        return $this;
    }

    /**
     * 合并一个数组到env
     *
     * @param array $env
     */
    public function push(array $env)
    {
        $_ENV = array_merge(array_change_key_case($env, CASE_UPPER), $_ENV);
    }

    /**
     * 获取env变量
     *
     * @param string $key     键名
     * @param null   $default 默认值
     *
     * @return array|ArrayAccess|mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($_ENV, strtoupper($key), $default);
    }

    /**
     * 全部ENV
     *
     * @return array
     */
    public function all(): array
    {
        return $_ENV;
    }

    /**
     * 判断是否存在
     *
     * @param $key
     *
     * @return bool
     */
    public function has($key): bool
    {
        return Arr::has($_ENV, strtoupper($key));
    }
}
