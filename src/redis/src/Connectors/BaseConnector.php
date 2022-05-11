<?php

namespace Max\Redis\Connectors;

use Max\Redis\RedisConfig;

class BaseConnector
{
    /**
     * @param RedisConfig $config
     */
    public function __construct(RedisConfig $config)
    {
    }

    /**
     * @return \Redis
     */
    public function get()
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
        if ($auth = $this->config->getAuth()) {
            $redis->auth($auth);
        }
        return $redis;
    }
}
