<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Connector;

use Max\Database\Contract\ConnectorInterface;
use Max\Database\DBConfig;
use Swoole\Coroutine;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class SwoolePoolConnector implements ConnectorInterface
{
    protected PDOPool $PDOPool;

    public function __construct(
        protected DBConfig $config
    ) {
        $PDOConfig     = (new PDOConfig())
            ->withDriver($this->config->getDriver())
            ->withHost($this->config->getHost())
            ->withPort($this->config->getPort())
            ->withUnixSocket($this->config->getUnixSocket())
            ->withCharset($this->config->getCharset())
            ->withDbname($this->config->getDatabase())
            ->withUsername($this->config->getUser())
            ->withPassword($this->config->getPassword())
            ->withOptions($this->config->getOptions());
        $this->PDOPool = new PDOPool($PDOConfig, $this->config->getPoolSize());
    }

    public function get()
    {
        $connection = $this->PDOPool->get();
        Coroutine::defer(function () use ($connection) {
            $this->PDOPool->put($connection);
        });
        return $connection;
    }

    public function release($connection)
    {
        $this->PDOPool->put($connection);
    }
}
