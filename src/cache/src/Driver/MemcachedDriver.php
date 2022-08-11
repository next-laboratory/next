<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Driver;

use Max\Utils\Traits\AutoFillProperties;

class MemcachedDriver extends AbstractDriver
{
    use AutoFillProperties;

    protected string $host = '127.0.0.1';

    protected int $port = 11211;

    protected int $weight = 0;

    protected \Memcached $memcached;

    public function __construct(array $options)
    {
        $this->fillProperties($options);
        $this->memcached = new \Memcached();
        $this->memcached->addServer($this->host, $this->port, $this->weight);
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
