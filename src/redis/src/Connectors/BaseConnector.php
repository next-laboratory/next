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
use Max\Redis\Redis;
use Max\Redis\RedisConfig;

class BaseConnector implements ConnectorInterface
{
    protected \ArrayObject $pool;

    public function __construct(protected RedisConfig $config)
    {
        $this->pool = new \ArrayObject();
    }

    public function get(): \Redis
    {
        $name = $this->config->getName();
        if (!$this->pool->offsetExists($name)) {
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
            $this->pool->offsetSet($name, $redis);
        }

        $redis = $this->pool->offsetGet($name);
        $this->pool->offsetUnset($name);
        return $redis;
    }

    public function release($redis)
    {
        $this->pool->offsetSet($this->config->getName(), $redis);
    }
}
