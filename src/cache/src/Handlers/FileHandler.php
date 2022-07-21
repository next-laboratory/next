<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use Exception;
use Max\Cache\Exceptions\CacheException;
use Throwable;
use function chmod;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function glob;
use function is_dir;
use function is_file;
use function is_readable;
use function is_writable;
use function md5;
use function mkdir;
use function rmdir;
use function rtrim;
use function serialize;
use function strtolower;
use function unlink;

class FileHandler extends CacheHandler
{
    /**
     * 缓存路径.
     */
    protected string $path;

    /**
     * 初始化
     * File constructor.
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        if (file_exists($path = $config['path'])) {
            if (is_file($path)) {
                throw new CacheException('已经存在同名文件，不能创建文件夹!');
            }
            if (!is_writable($path) || !is_readable($path)) {
                chmod($path, 0755);
            }
        } else {
            mkdir($path, 0755, true);
        }
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . '/';
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        try {
            $cacheFile = $this->getFile($key);
            if (file_exists($cacheFile)) {
                $expire = (int) (unserialize($this->getCache($cacheFile))[0]);
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

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        if ($this->has($key)) {
            return $this->remove($key);
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return unserialize($this->getCache($this->getFile($key)))[1];
        }
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return (bool) file_put_contents($this->getFile($key), serialize([(int) $ttl, $value]));
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        try {
            $this->unlink($this->path);
            return true;
        } catch (Throwable) {
            return false;
        }
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
