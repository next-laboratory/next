<?php

namespace Max\Pool;

use Max\Pool\Contracts\PoolInterface;
use Swoole\Coroutine\Channel;

abstract class CoroutinePool implements PoolInterface
{
    /**
     * 池
     *
     * @var Channel
     */
    protected Channel $pool;

    /**
     * 容量
     *
     * @var int
     */
    protected int $capacity = 64;

    /**
     * 大小
     *
     * @var int
     */
    protected int $size = 0;

    public function open()
    {
        $this->pool = new Channel($this->capacity);
    }

    /**
     * 新连接
     *
     * @return object
     */
    abstract protected function new();

    /**
     * 取
     *
     * @return mixed
     */
    public function get()
    {
        if ($this->size < $this->capacity) {
            $connection = $this->new();
            $this->size++;
        } else {
            $connection = $this->pool->pop(3);
        }
        return $connection;
    }

    /**
     * 归还连接，如果连接不能使用则归还null
     *
     * @param $connection
     */
    public function put($connection): void
    {
        if (is_null($connection)) {
            $this->size--;
        } else if (!$this->pool->isFull()) {
            $this->pool->push($connection);
        }
    }

    /**
     * 填充连接池
     */
    public function fill(): void
    {
        for ($i = 0; $i < $this->capacity; $i++) {
            $this->put($this->new());
        }
        $this->size = $this->capacity;
    }
}
