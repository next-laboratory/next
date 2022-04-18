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

namespace Max\Config;

use Max\Utils\Arr;
use function pathinfo;

class Repository
{
    /**
     * 配置数组
     *
     * @var array
     */
    protected array $items = [];

    /**
     * 获取[支持点语法]
     *
     * @param string|null $key
     * @param null        $default
     *
     * @return mixed
     */
    public function get(string $key = null, $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * 设置[支持点语法]
     *
     * @param string $key
     * @param        $value
     */
    public function set(string $key, $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * 全部
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * 加载多个配文件
     *
     * @param array $map
     */
    public function load(array $map): void
    {
        foreach ($map as $item) {
            $this->loadOne($item);
        }
    }

    /**
     * 加载一个配置文件
     *
     * @param string $config
     */
    public function loadOne(string $config): void
    {
        $this->items[pathinfo($config, PATHINFO_FILENAME)] = include_once $config;
    }
}
