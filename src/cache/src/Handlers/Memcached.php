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

namespace Max\Cache\Handlers;

use Max\Utils\Traits\AutoFillProperties;

class Memcached extends CacheHandler
{
    use AutoFillProperties;

    /**
     * @var string
     */
    protected string $host = '127.0.0.1';

    /**
     * @var int
     */
    protected int $port = 11211;

    /**
     * @var int
     */
    protected int $weight = 0;

    /**
     * 初始化
     * Memcached constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->fillProperties($options);
        $this->handler = new \Memcached();
        $this->handler->addServer($this->host, $this->port, $this->weight);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->handler->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->handler->set($key, serialize($value), (int)$ttl);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $status = $this->handler->get($key);
        return false !== $status && !is_null($status);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->handler->flush();
    }
}
