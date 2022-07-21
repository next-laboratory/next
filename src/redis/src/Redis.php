<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Redis;

use Closure;
use Max\Redis\Contracts\ConnectorInterface;
use Throwable;

/**
 * @mixin \Redis
 */
class Redis
{
    public function __construct(
        protected ConnectorInterface $connector
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        return $this->connector->get()->{$name}(...$arguments);
    }

    /**
     * @param  null|mixed $redis
     * @throws Throwable
     */
    public function wrap(Closure $wrapper, $redis = null)
    {
        return $wrapper($redis ?? $this->connector->get());
    }

    /**
     * @throws Throwable
     */
    public function multi(Closure $wrapper, int $mode = \Redis::MULTI)
    {
        return $this->wrap(function ($redis) use ($wrapper, $mode) {
            try {
                /* @var \Redis $redis */
                $redis->multi($mode);
                $result = $wrapper($redis);
                $redis->exec();
                return $result;
            } catch (Throwable $throwable) {
                $redis->discard();
                throw $throwable;
            }
        });
    }
}
