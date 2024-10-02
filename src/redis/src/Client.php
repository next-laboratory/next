<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Redis;

use Closure;
use Next\Redis\Contract\ConnectorInterface;
use Redis;
use Throwable;

/**
 * @mixin Redis
 */
class Client
{
    public function __construct(
        protected ConnectorInterface $connector
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        return $this->wrap(function ($redis) use ($name, $arguments) {
            return $redis->{$name}(...$arguments);
        });
    }

    public function multi(Closure $callback, int $mode = Redis::MULTI)
    {
        return $this->wrap(function (Redis $redis) use ($callback, $mode) {
            try {
                $redis = $redis->multi($mode);
                $result = $callback($redis);
                $redis->exec();
                return $result;
            } catch (Throwable $e) {
                $redis->discard();
                throw $e;
            }
        });
    }

    /**
     * @param string|string[] $key
     * @throws Throwable
     */
    public function watch(string|array $key, Closure $callback)
    {
        return $this->wrap(function (Redis $redis) use ($callback, $key) {
            $redis->watch($key);
            return $callback($redis);
        });
    }

    /**
     * @throws Throwable
     */
    public function wrap(Closure $callable)
    {
        try {
            $redis = $this->connector->get();
            return $callable($redis);
        } catch (Throwable $e) {
            $redis = null;
            throw $e;
        } finally {
            $this->connector->release($redis);
        }
    }
}
