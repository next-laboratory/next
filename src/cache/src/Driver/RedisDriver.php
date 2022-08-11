<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Driver;

use Max\Redis\Connector\BaseConnector;
use Max\Redis\Redis;
use Max\Utils\Traits\AutoFillProperties;

class RedisDriver extends AbstractDriver
{
    use AutoFillProperties;

    protected string $connector = BaseConnector::class;

    protected array  $config    = [];

    protected Redis  $redis;

    public function __construct(array $config)
    {
        $this->fillProperties($config);
        $this->redis = new Redis(new $this->connector());
    }

    public function delete($key): bool
    {
        return (bool) $this->redis->del($key);
    }

    public function has($key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    public function clear(): bool
    {
        return $this->redis->flushAll();
    }

    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        return $this->redis->set($key, $value, $ttl);
    }
}
