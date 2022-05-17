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

namespace Max\Redis\Connectors;

use ArrayObject;
use Max\Context\Context;
use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;
use Max\Redis\Context\Connection;
use Max\Redis\RedisConfig;
use Redis;
use Swoole\Coroutine\Channel;

class PoolConnector implements PoolInterface
{
    /**
     * @var Channel
     */
    protected Channel $pool;

    /**
     * 容量
     *
     * @var int
     */
    protected int $capacity;

    /**
     * 大小
     *
     * @var int
     */
    protected int $size = 0;

    /**
     * @param RedisConfig $config
     */
    public function __construct(protected RedisConfig $config)
    {
        $this->pool = new Channel($this->capacity = $config->getPoolSize());
        if ($config->isAutofill()) {
            $this->fill();
        }
    }

    /**
     * 取
     *
     * @return mixed
     */
    public function get(): Poolable
    {
        $name = $this->config->getName();
        $key  = Connection::class;
        if (!Context::has($key)) {
            Context::put($key, new Connection());
        }
        /** @var ArrayObject $connection */
        $connection = Context::get($key);
        if (!$connection->offsetExists($name)) {
            if ($this->size < $this->capacity) {
                $redis = $this->create();
            } else {
                /** @var Redis $redis */
                $redis = $this->pool->pop(3);
                if (!$redis->isConnected()) {
                    $this->connect($redis);
                }
            }

            $connection->offsetSet($name, [
                'pool' => $this,
                'item' => $redis,
            ]);
        }

        return $connection->offsetGet($name)['item'];
    }

    /**
     * @return Poolable
     */
    protected function create()
    {
        $redis = new Redis();
        $this->connect($redis);
        $this->size++;

        return new \Max\Redis\Redis($this, $redis);
    }

    /**
     * @param Redis $redis
     *
     * @return void
     */
    protected function connect(Redis $redis): void
    {
        $redis->connect(
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getTimeout(),
            $this->config->getReserved(),
            $this->config->getRetryInterval(),
            $this->config->getReadTimeout()
        );
        $redis->select($this->config->getDatabase());
        if ($auth = $this->config->getAuth()) {
            $redis->auth($auth);
        }
    }

    /**
     * 归还连接，如果连接不能使用则归还null
     *
     * @param $redis
     */
    public function put($redis)
    {
        if (is_null($redis)) {
            $this->size--;
        } else if (!$this->pool->isFull()) {
            $this->pool->push($redis);
        }
    }

    /**
     * 填充连接池
     */
    public function fill()
    {
        for ($i = 0; $i < $this->capacity; $i++) {
            $this->put($this->create());
        }
        $this->size = $this->capacity;
    }

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function gc()
    {
        // TODO: Implement gc() method.
    }

    public function release()
    {
        $this->size--;
    }
}
