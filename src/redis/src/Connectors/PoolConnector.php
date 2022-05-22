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

namespace Max\Redis\Connectors;

use Max\Redis\Contracts\ConnectorInterface;
use Max\Redis\RedisConfig;
use Swoole\Database\RedisPool;

class PoolConnector implements ConnectorInterface
{
    protected RedisPool $pool;

    /**
     * @param RedisConfig $config
     */
    public function __construct(protected RedisConfig $config)
    {
        $this->pool = new RedisPool((new \Swoole\Database\RedisConfig())
            ->withHost($this->config->getHost())
            ->withPort($this->config->getPort())
            ->withTimeout($this->config->getTimeout())
            ->withReadTimeout($this->config->getReadTimeout())
            ->withRetryInterval($this->config->getRetryInterval())
            ->withReserved($this->config->getReserved())
            ->withDbIndex($this->config->getDatabase())
            ->withAuth($this->config->getAuth()),
            $this->config->getPoolSize()
        );
    }

    public function get(): \Redis
    {
        try {
            $redis = $this->pool->get();
            $redis->ping();
            return $redis;
        } catch (\RedisException $redisException) {
            $this->pool->put(null);
            throw $redisException;
        }
    }

    public function release(\Redis $redis)
    {
        $this->pool->put($redis);
    }
}
