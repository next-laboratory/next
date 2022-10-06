<?php

namespace Max\Redis\Connector;

use Max\Pool\BasePool;
use Max\Pool\BasePoolItem;
use Max\Pool\Contract\PoolItemInterface;
use Max\Redis\Contract\ConnectorInterface;
use Redis;
use RedisException;

class BasePoolConnector extends BasePool implements ConnectorInterface
{
    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 6379,
        protected float $timeout = 0.0,
        protected $reserved = null,
        protected int $retryInterval = 0,
        protected float $readTimeout = 0.0,
        protected string $auth = '',
        protected int $database = 0,
        protected int $poolSize = 16,
    ) {
        $this->open();
    }

    public function getPoolCapacity(): int
    {
        return $this->poolSize;
    }

    /**
     * @throws RedisException
     */
    public function newPoolItem()
    {
        $redis = new Redis();
        $redis->pconnect(
            $this->host,
            $this->port,
            $this->timeout,
            $this->reserved,
            $this->retryInterval,
            $this->readTimeout
        );
        $redis->select($this->database);
        $this->auth && $redis->auth($this->auth);
        return $redis;
    }
}
