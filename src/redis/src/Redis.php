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
     * @var int
     */
    protected int $commandProcessed = 0;

    /**
     * @param PoolInterface $pool
     * @param \Redis $redis
     */
    public function __construct(protected PoolInterface $pool, protected \Redis $redis)
    {
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws \RedisException
     */
    public function __call(string $name, array $arguments)
    {
        try {
            $result = $this->redis->{$name}(...$arguments);
            $this->pool->release($this->redis);
            $this->commandProcessed++;
            return $result;
        } catch (\RedisException $redisException) {
            $this->pool->release(null);
            throw $redisException;
        }
    }
}
