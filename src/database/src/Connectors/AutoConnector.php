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

use Max\Database\Contracts\ConnectorInterface;
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

    /**
     * @var array
     */
    protected array $container = [];

    /**
     * @param DatabaseConfig $config
     */
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
        if (!isset($this->container[$type])) {
            $connector = $this->connectors[$type];
            $this->container[$type] = new $connector($this->config);
        }
        return $this->container[$type]->get();
    }
}
