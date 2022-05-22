<?php

namespace Max\Redis;

use Max\Redis\Contracts\ConnectorInterface;

/**
 * @mixin \Redis
 */
class Redis
{
    protected \Redis $redis;

    public function __construct(protected ConnectorInterface $connector)
    {
        $this->redis = $this->connector->get();
    }

    /**
     * @throws \RedisException
     */
    public function __call(string $name, array $arguments)
    {
        return $this->redis->{$name}(...$arguments);
    }

    public function __destruct()
    {
        $this->connector->release($this->redis);
    }
}
