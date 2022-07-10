<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

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

    public function __destruct()
    {
        $this->connector->release($this->redis);
    }

    /**
     * @throws \RedisException
     */
    public function __call(string $name, array $arguments)
    {
        return $this->redis->{$name}(...$arguments);
    }
}
