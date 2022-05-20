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
use Max\Utils\Arr;
use function strtoupper;

class Env
{
    /**
     * @param string $iniFile
     *
     * @return void
     */
    public static function loadFromIniFile(string $iniFile): void
    {
        static::loadFromArray(parse_ini_file($iniFile, true, INI_SCANNER_TYPED));
    }

    /**
     * @param string $jsonFile
     *
     * @return void
     */
    public static function loadFromJsonFile(string $jsonFile): void
    {
        static::loadFromJson(file_get_contents($jsonFile));
    }

    /**
     * @param string $json
     *
     * @return void
     */
    public static function loadFromJson(string $json): void
    {
        static::loadFromArray(json_decode($json, true));
    }

    /**
     * @param array $env
     *
     * @return void
     */
    public static function loadFromArray(array $env): void
    {
        $_ENV = array_merge($_ENV, array_change_key_case($env, CASE_UPPER));
    }

    /**
     * 设置env
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        $_ENV[strtoupper($key)] = $value;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public static function remove(string $key): void
    {
        Arr::forget($_ENV, strtoupper($key));
    }

    /**
     * 获取env变量
     *
     * @param string $key     键名
     * @param null   $default 默认值
     *
     * @return array|ArrayAccess|mixed
     */
    public static function get(string $key, $default = null): mixed
    {
        return Arr::get($_ENV, strtoupper($key), $default);
    }

    /**
     * 判断是否存在
     *
     * @param $key
     *
     * @return bool
     */
    public static function has($key): bool
    {
        return Arr::has($_ENV, strtoupper($key));
    }
}
