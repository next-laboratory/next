<?php

namespace Max\Redis;

use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;

/**
 * @mixin \Redis
 */
class Redis implements Poolable
{
    /**
     * @param PoolInterface $pool
     */
    public function __construct(protected PoolInterface $pool)
    {
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \RedisException
     */
    public function __call(string $name, array $arguments)
    {
        try {
            $redis  = $this->pool->get();
            $result = $redis->{$name}(...$arguments);
            $this->pool->put($redis);
            return $result;
        } catch (\RedisException $redisException) {
            $this->pool->release();
            throw $redisException;
        }
    }
}
