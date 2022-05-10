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

use Max\Config\Contracts\ConfigInterface;
use Max\Utils\Arr;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function pathinfo;

class Repository implements ConfigInterface
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
    public function get(string $key, $default = null): mixed
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
     * 扫描目录
     *
     * @param string|array $dirs
     *
     * @return void
     */
    public function scan(string|array $dirs): void
    {
        foreach ((array)$dirs as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $path = $file->getRealPath() ?: $file->getPathname();
                if ('php' !== pathinfo($path, PATHINFO_EXTENSION)) {
                    continue;
                }
                $this->loadOne($path);
                gc_mem_caches();
            }
        }
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
     * @param string|array $files
     */
    public function load(string|array $files): void
    {
        is_array($files) ? $this->loadMany($files) : $this->loadOne($files);
    }

    /**
     * @param array $files
     *
     * @return void
     */
    public function loadMany(array $files): void
    {
        foreach ($files as $file) {
            $this->loadOne($file);
        }
    }

    /**
     * 加载一个配置文件
     *
     * @param string $file
     */
    public function loadOne(string $file): void
    {
        $this->items[pathinfo($file, PATHINFO_FILENAME)] = include_once $file;
    }
}
