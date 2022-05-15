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
use Max\Redis\Context\Connection;
use Max\Redis\RedisConfig;
use Swoole\Coroutine\Channel;

class PoolConnector
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
    public function get()
    {
        $name = $this->config->getName();
        $key  = Connection::class;
        // TODO 连接出错
        if (!Context::has($key)) {
            $connection = new Connection();
            if ($this->size < $this->capacity) {
                $PDO = $this->create();
                $this->size++;
            } else {
                $PDO = $this->pool->pop(3);
            }
            $connection->offsetSet($name, [
                'pool' => $this,
                'item' => $PDO,
            ]);
            Context::put($key, $connection);
        }
        return Context::get($key)[$name]['item'];
    }

    /**
     * @return \Redis
     */
    protected function create()
    {
        $redis = new \Redis();
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
        return $redis;
    }

    /**
     * 归还连接，如果连接不能使用则归还null
     *
     * @param $PDO
     */
    public function put($PDO)
    {
        if (is_null($PDO)) {
            $this->size--;
        } else if (!$this->pool->isFull()) {
            $this->pool->push($PDO);
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
}
