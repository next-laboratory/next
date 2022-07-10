<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use Max\Utils\Traits\AutoFillProperties;

class MemcachedHandler extends CacheHandler
{
    use AutoFillProperties;

    protected string $host = '127.0.0.1';

    protected int $port = 11211;

    protected int $weight = 0;

    /**
     * 初始化
     * Memcached constructor.
     */
    public function __construct(array $options)
    {
        $this->fillProperties($options);
        $this->handler = new \Memcached();
        $this->handler->addServer($this->host, $this->port, $this->weight);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return $this->handler->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->handler->set($key, serialize($value), (int) $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $status = $this->handler->get($key);
        return $status !== false && ! is_null($status);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->handler->flush();
    }
}
