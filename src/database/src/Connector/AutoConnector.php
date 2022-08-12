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
use Max\Database\DatabaseConfig;

class AutoConnector implements ConnectorInterface
{
    /**
     * @var array|string[]
     */
    protected array $connectors = [
        'pool' => PoolConnector::class,
        'base' => BaseConnector::class,
    ];

    protected array $container = [];

    public function __construct(protected DatabaseConfig $config)
    {
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $type = 'base';
        if (class_exists('Swoole\Coroutine')) {
            if (\Swoole\Coroutine::getCid() > 0) {
                $type = 'pool';
            }
        }
        if (! isset($this->container[$type])) {
            $connector              = $this->connectors[$type];
            $this->container[$type] = new $connector($this->config);
        }
        return $this->container[$type]->get();
    }
}
