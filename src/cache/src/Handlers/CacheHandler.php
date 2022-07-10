<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use Closure;
use Exception;
use Max\Cache\Contracts\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

abstract class CacheHandler implements CacheInterface
{
    /**
     * @var mixed
     */
    protected $handler;

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $data = $this->handler->get($key);
        return is_null($data) ? value($default) : unserialize((string) $data);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->handler->set($key, serialize($value), $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        return array_reduce((array) $keys, function ($stack, $key) use ($default) {
            $stack[$key] = $this->has($key) ? $this->get($key) :
                (is_array($default) ? ($default[$key] ?? null) : $default);
            return $stack;
        }, []);
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        try {
            foreach ((array) $values as $key => $value) {
                $this->set($key, $value, $ttl);
            }
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        try {
            foreach ((array) $keys as $key) {
                $this->delete($key);
            }
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * 记住缓存并返回.
     *
     * @param $key
     *
     * @throws InvalidArgumentException
     */
    public function remember($key, Closure $callback, ?int $ttl = null): mixed
    {
        if (! $this->has($key)) {
            $this->set($key, $callback(), $ttl);
        }
        return $this->get($key);
    }

    /**
     * 自增.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function incr($key, int $step = 1): bool
    {
        return (bool) $this->set($key, (int) $this->get($key) + $step);
    }

    /**
     * 自减去.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function decr($key, int $step = 1): bool
    {
        return $this->incr($key, -$step);
    }

    /**
     * 取出并删除.
     *
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function pull($key): mixed
    {
        $value = $this->get($key);
        $this->delete($key);
        return $value;
    }
}
