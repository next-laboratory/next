<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache;

use Closure;
use Exception;
use Max\Cache\Contract\CacheDriverInterface;
use Max\Cache\Contract\CacheInterface;

use function Max\Utils\value;

class Cache implements CacheInterface
{
    /**
     * @param CacheDriverInterface $driver
     */
    public function __construct(
        protected CacheDriverInterface $driver
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->driver->has($key);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if (! $this->has($key)) {
            return value($default, $key);
        }
        return $this->driver->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->driver->set($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->driver->clear();
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
     * @param string $key
     * @param Closure $callback
     * @param int|null $ttl
     * @return mixed
     */
    public function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        if (! $this->has($key)) {
            $this->set($key, $callback(), $ttl);
        }
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param int $step
     * @return int
     */
    public function increment(string $key, int $step = 1): int
    {
        return $this->driver->increment($key, $step);
    }

    /**
     * @param string $key
     * @param int $step
     * @return int
     */
    public function decrement(string $key, int $step = 1): int
    {
        return $this->driver->decrement($key, $step);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function pull(string $key): mixed
    {
        $value = $this->get($key);
        $this->delete($key);
        return $value;
    }
}
