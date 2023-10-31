<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Cache\Driver;

class MemcachedDriver extends AbstractDriver
{
    protected \Memcached $memcached;

    public function __construct()
    {
        $this->memcached = new \Memcached();
    }

    public function addServer($host = '127.0.0.1', $port = 11211, $weight = 0): void
    {
        $this->memcached->addServer($host, $port, $weight);
    }

    public function delete($key): bool
    {
        return $this->memcached->delete($key);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->memcached->set($key, serialize($value), (int) $ttl);
    }

    public function has($key): bool
    {
        $status = $this->memcached->get($key);
        return $status !== false && ! is_null($status);
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    public function get(string $key): mixed
    {
        return $this->memcached->get($key);
    }
}
