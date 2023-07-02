<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Driver;

class RedisDriver extends AbstractDriver
{
    protected \Redis $redis;

    /**
     * @throws \RedisException
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        float $timeout = 0,
        string|null $persistentId = null,
        int $retryInterval = 0,
        float $readTimeout = 0,
        array $context = [],
        string $password = '',
        int $database = 0,
        protected string $cachePrefix = '',
    ) {
        $this->redis = new \Redis();
        if ($this->redis->connect($host, $port, $timeout, $persistentId, $retryInterval, $readTimeout, $context)) {
            $this->redis->select($database);
            if ($password) {
                $this->redis->auth($password);
            }
        }
    }

    /**
     * @param  mixed           $key
     * @throws \RedisException
     */
    public function delete($key): bool
    {
        return (bool) $this->redis->del($this->normalizeKey($key));
    }

    /**
     * @param  mixed           $key
     * @throws \RedisException
     */
    public function has($key): bool
    {
        return (bool) $this->redis->exists($this->normalizeKey($key));
    }

    /**
     * @throws \RedisException
     */
    public function clear(): bool
    {
        return $this->redis->eval(<<<'LUA'
 local keys = redis.call('keys', ARGV[1])
    for _, key in ipairs(keys) do
        redis.call('del', key)
    end
LUA
            , [$this->normalizeKey('*')]);
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): mixed
    {
        return $this->redis->get($this->normalizeKey($key));
    }

    /**
     * @throws \RedisException
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->redis->set($this->normalizeKey($key), $value, $ttl);
    }

    protected function normalizeKey($id)
    {
        $key = 'cache:' . $id;
        if ($this->cachePrefix) {
            $key = $this->cachePrefix . ':' . $key;
        }

        return $key;
    }
}
