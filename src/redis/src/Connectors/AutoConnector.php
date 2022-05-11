<?php

namespace Max\Redis\Connectors;

use Max\Redis\RedisConfig;
use Swoole\Coroutine;

class AutoConnector
{
    /**
     * @var array|string[]
     */
    protected array $connectors = [
        'pool' => PoolConnector::class,
        'base' => BaseConnector::class,
    ];

    /**
     * @var array
     */
    protected array $container = [];

    /**
     * @param RedisConfig $config
     */
    public function __construct(protected RedisConfig $config)
    {
    }

    /**
     * @return mixed
     */
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
