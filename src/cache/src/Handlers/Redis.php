<?php
declare(strict_types=1);

namespace Max\Cache\Handlers;

use Max\Database\Redis as DatabaseRedis;

class Redis extends CacheHandler
{
    /**
     * 初始化
     * Redis constructor.
     *
     * @param DatabaseRedis $redis
     */
    public function __construct(DatabaseRedis $redis)
    {
        $this->handler = $redis;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return (bool)$this->handler->del($key);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return (bool)$this->handler->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->handler->flushAll();
    }
}
