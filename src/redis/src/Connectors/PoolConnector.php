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

use Max\Context\Context;
use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;
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
     * @return \Max\Redis\Redis
     */
    public function get(): Poolable
    {
        // TOOD 多携程，多配置下会有问题
        $key = \Max\Redis\Redis::class;
        if (!Context::has($key)) {
            if ($this->size < $this->capacity) {
                $redis = $this->create();
            } else {
                $redis = $this->pool->pop(3);
                dump(spl_object_hash($redis));
            }
            Context::put($key, $redis);
        }
        return Context::get($key);
    }

    /**
     * @return \Max\Redis\Redis
     */
    protected function create()
    {
        $redis = new \Redis();
        $this->connect($redis);
        $this->size++;
        return new \Max\Redis\Redis($this, $redis);
    }

    /**
     * @param \Redis $redis
     *
     * @return void
     */
    protected function connect(\Redis $redis): void
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
     * 填充连接池
     */
    public function fill()
    {
        for ($i = 0; $i < $this->capacity; $i++) {
            $this->release($this->create());
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

    public function release(?Poolable $poolable)
    {
        if (is_null($poolable)) {
            $this->size--;
        } else if (!$this->pool->isFull()) {
            $this->pool->push($poolable);
        }
    }
}
