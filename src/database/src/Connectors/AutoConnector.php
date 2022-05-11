<?php

namespace Max\Database\Connectors;

use Max\Database\Contracts\ConnectorInterface;
use Max\Database\DatabaseConfig;
use Swoole\Coroutine;

class AutoConnector implements ConnectorInterface
{
    protected array $connectors = [
        'pool' => PoolConnector::class,
        'base' => BaseConnector::class,
    ];

    protected array $container = [];

    public function __construct(protected DatabaseConfig $config)
    {
    }

    public function get()
    {
        $type = class_exists(Coroutine::class) && Coroutine::getCid() > 0 ? 'pool' : 'base';
        if (!isset($this->container[$type])) {
            $connector              = $this->connectors[$type];
            $this->container[$type] = new $connector($this->config);
        }
        return $this->container[$type]->get();
    }
}
