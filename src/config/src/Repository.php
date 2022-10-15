<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Config;

use Max\Config\Contract\ConfigInterface;
use Max\Utils\Arr;
use Max\Utils\Filesystem;

use function pathinfo;

class Repository implements ConfigInterface
{
    /**
     * 配置数组.
     */
    protected array $items = [];

    /**
     * 获取[支持点语法].
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * 设置[支持点语法]. Swoole/WorkerMan等环境下不可使用.
     */
    public function set(string $key, mixed $value): void
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * 扫描目录.
     */
    public function scan(string|array $dirs): void
    {
        $files = (new Filesystem())->files($dirs, pattern: '*.php');
        foreach ($files as $file) {
            $this->loadOne($file->getRealPath());
        }
        //
        //
        //        foreach ((array)$dirs as $dir) {
        //            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        //            /** @var SplFileInfo $file */
        //            foreach ($files as $file) {
        //                if (!$file->isFile()) {
        //                    continue;
        //                }
        //                $path = $file->getRealPath() ?: $file->getPathname();
        //                if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
        //                    continue;
        //                }
        //                $configFiles[] = $path;
        //                gc_mem_caches();
        //            }
        //        }
        //        $this->load($configFiles);
    }

    /**
     * 全部.
     */
    public function all(): array
    {
        return $this->items;
    }

    public function loadOne(string $file)
    {
        $this->items[pathinfo($file, PATHINFO_FILENAME)] = include_once $file;
    }

    /**
     * 加载配置.
     */
    public function load(string|array $files): void
    {
        foreach ((array) $files as $file) {
            $this->loadOne($file);
        }
    }
}
