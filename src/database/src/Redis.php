<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Database;

use Closure;
use Exception;
use Max\Database\Pools\RedisPool;
use Throwable;

/**
 * Class Redis
 *
 * @mixin \Redis
 * @package Max\Database
 */
class Redis
{
    /**
     * It will be the default connection while the connection property is null.
     *
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * Redis constructor.
     *
     * @param RedisPool $pool
     */
    public function __construct(protected RedisPool $pool)
    {
    }

    /**
     * Return a new redis proxy with new connection.
     *
     * @param string $connection
     *
     * @return Redis
     */
    public function connection(string $connection): static
    {
        $new             = clone $this;
        $new->connection = $connection;

        return $new;
    }

    /**
     * The function will be called in order to put the connection into pool after query.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     * @throws Exception|Throwable
     */
    public function __call($method, $arguments)
    {
        return $this->wrap(function($redis) use ($method, $arguments) {
            return $redis->{$method}(...$arguments);
        }, $this->connection);
    }

    /**
     * @param Closure     $closure
     * @param string|null $connection
     *
     * @return mixed
     * @throws Throwable
     */
    protected function wrap(Closure $closure, ?string $connection = null): mixed
    {
        $pool  = $this->pool->getPool($connection);
        $redis = $pool->get();
        try {
            $result = $closure($redis);
            $pool->put($redis);
            return $result;
        } catch (Throwable $throwable) {
            $pool->put(null);
            throw $throwable;
        }
    }
}
