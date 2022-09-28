<?php

namespace Max\Redis\Connector;

use Max\Redis\Contract\ConnectorInterface;
use Redis;
use RuntimeException;
use SplQueue;

class BasePoolConnector implements ConnectorInterface
{
    protected SplQueue $splQueue;
    protected int $num = 0;

    public function __construct(
        protected string $host = '127.0.0.1',
        protected int    $port = 6379,
        protected float  $timeout = 0.0,
        protected        $reserved = null,
        protected int    $retryInterval = 0,
        protected float  $readTimeout = 0.0,
        protected string $auth = '',
        protected int    $database = 0,
        protected int    $poolSize = 16,
    )
    {
        $this->splQueue = new SplQueue();
    }

    public function get()
    {
        if ($this->isEmpty() && $this->num >= $this->poolSize) {
            throw new RuntimeException('Too many connections');
        }
        if ($this->num < $this->poolSize) {
            $this->splQueue->push($this->newConnection());
            $this->num++;
        }
        return $this->splQueue->shift();
    }

    public function release($connection)
    {
        if (is_null($connection)) {
            $this->num--;
        } else if (!$this->isFull()) {
            $this->splQueue->push($connection);
        }
    }

    protected function isFull(): bool
    {
        return $this->splQueue->count() >= $this->poolSize;
    }

    protected function isEmpty(): bool
    {
        return $this->splQueue->isEmpty();
    }

    protected function newConnection(): Redis
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