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

namespace Max\Database\Connectors;

use ArrayObject;
use Max\Context\Context;
use Max\Database\Context\Connection;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\Contracts\PoolInterface;
use Max\Database\DatabaseConfig;
use PDO;
use Swoole\Coroutine\Channel;

class PoolConnector implements ConnectorInterface, PoolInterface
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
     * @param DatabaseConfig $config
     */
    public function __construct(protected DatabaseConfig $config)
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
        if (!Context::has($key)) {
            Context::put($key, new Connection());
        }
        /** @var ArrayObject $connection */
        $connection = Context::get($key);
        $connection->offsetSet($name, [
            'pool' => $this,
            'item' => $this->size < $this->capacity ? $this->create() : $this->pool->pop(3),
        ]);
        return $connection->offsetGet($name)['item'];
    }

    /**
     * @return PDO
     */
    protected function create()
    {
        $PDO = new PDO(
            $this->config->getDsn(),
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getOptions()
        );
        $this->size++;

        return $PDO;
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
