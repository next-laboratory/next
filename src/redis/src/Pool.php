<?php

namespace Max\Redis;

use Swoole\Coroutine\Channel;

class Pool
{
    protected Channel $pool;

    public function __construct()
    {
        $this->pool = new Channel(32);
    }

    public function get()
    {
        $this->pool->push($this->create());
        return $this->pool->pop();
    }

    public function put()
    {
        $this->pool->push($this->create());
    }

    public function create()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        return $redis;
    }
}
