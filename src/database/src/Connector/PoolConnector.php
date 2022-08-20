<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Connector;

use ArrayObject;
use Max\Context\Context;
use Max\Database\Context\Connection;
use Max\Database\Contract\ConnectorInterface;
use Max\Database\Contract\PoolInterface;
use Max\Database\DatabaseConfig;
use PDO;
use Swoole\Coroutine\Channel;

class PoolConnector implements ConnectorInterface, PoolInterface
{
    protected Channel $pool;

    /**
     * 容量.
     */
    protected int $capacity;

    /**
     * 大小.
     */
    protected int $size = 0;

    public function __construct(
        protected DatabaseConfig $config
    ) {
        $this->pool = new Channel($this->capacity = $config->getPoolSize());
        if ($config->isAutofill()) {
            $this->fill();
        }
    }

    /**
     * 取.
     *
     * @return mixed
     */
    public function get()
    {
        $name = $this->config->getName();
        $key  = Connection::class;
        if (! Context::has($key)) {
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
     * 归还连接，如果连接不能使用则归还null.
     *
     * @param $PDO
     */
    public function put($PDO)
    {
        if (is_null($PDO)) {
            --$this->size;
        } elseif (! $this->pool->isFull()) {
            $this->pool->push($PDO);
        }
    }

    /**
     * 填充连接池.
     */
    public function fill()
    {
        for ($i = 0; $i < $this->capacity; ++$i) {
            $this->put($this->create());
        }
        $this->size = $this->capacity;
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
        ++$this->size;

        return $PDO;
    }
}
