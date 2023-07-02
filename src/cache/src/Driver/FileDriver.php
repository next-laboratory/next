<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Driver;

use Max\Cache\Exception\CacheException;
use Throwable;

class FileDriver extends AbstractDriver
{
    /**
     * @throws CacheException
     */
    public function __construct(
        protected string $path
    ) {
        if (file_exists($path)) {
            if (is_file($path)) {
                throw new CacheException('The folder cannot be created as a file with the same name already exists!');
            }
            if (! is_writable($path) || ! is_readable($path)) {
                chmod($path, 0755);
            }
        } else {
            mkdir($path, 0755, true);
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . '/';
    }

    public function has(string $key): bool
    {
        try {
            $cacheFile = $this->getFile($key);
            if (file_exists($cacheFile)) {
                $expire = (int) unserialize($this->getCache($cacheFile))[0];
                if ($expire !== 0 && filemtime($cacheFile) + $expire < time()) {
                    $this->remove($key);
                    return false;
                }
                return true;
            }
            return false;
        } catch (Throwable) {
            return false;
        }
    }

    public function get(string $key): mixed
    {
        if ($this->has($key)) {
            return unserialize($this->getCache($this->getFile($key)))[1];
        }
        return null;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return (bool) file_put_contents($this->getFile($key), serialize([(int) $ttl, $value]));
    }

    public function clear(): bool
    {
        try {
            $this->unlink($this->path);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            return $this->remove($key);
        }
        return true;
    }

    /**
     * TODO 使用yield优化.
     */
    protected function unlink(string $dir)
    {
        foreach (glob(rtrim($dir, '/') . '/*') as $item) {
            if (is_dir($item)) {
                $this->unlink($item);
                rmdir($item);
            } else {
                unlink($item);
            }
        }
    }

    /**
     * 取得缓存内容.
     */
    protected function getCache(string $cacheFile): bool|string
    {
        return file_get_contents($cacheFile);
    }

    /**
     * 缓存hash.
     */
    protected function getID(string $key): string
    {
        return md5(strtolower($key));
    }

    /**
     * 删除某一个缓存，必须在已知缓存存在的情况下调用，否则会报错.
     */
    protected function remove(string $key): bool
    {
        return unlink($this->getFile($key));
    }

    /**
     * 根据key获取文件.
     */
    protected function getFile(string $key): string
    {
        return $this->path . $this->getID($key);
    }
}
