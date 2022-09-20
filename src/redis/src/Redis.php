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
use Max\Redis\Contract\ConnectorInterface;
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

    /**
     * @throws Throwable
     */
    public function __call(string $name, array $arguments)
    {
        return $this->wrap(function($redis) use ($name, $arguments) {
            return $redis->{$name}(...$arguments);
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
