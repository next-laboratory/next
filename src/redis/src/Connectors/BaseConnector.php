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

use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;
use Max\Redis\Redis;
use Max\Redis\RedisConfig;

class BaseConnector implements PoolInterface
{
    /**
     * @param RedisConfig $config
     */
    public function __construct(protected RedisConfig $config)
    {
    }

    /**
     * @return \Redis
     */
    public function get(): Poolable
    {
        $redis = new \Redis();
        $redis->connect(
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getTimeout(),
            $this->config->getReserved(),
            $this->config->getRetryInterval(),
            $this->config->getReadTimeout()
        );
        $redis->select($this->config->getDatabase());
        if ($auth = $this->config->getAuth()) {
            $redis->auth($auth);
        }

        return new Redis($this, $redis);
    }

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function gc()
    {
        // TODO: Implement gc() method.
    }

    public function release(?Poolable $poolable)
    {
    }
}
