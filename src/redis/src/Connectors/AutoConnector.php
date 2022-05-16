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

use Max\Redis\Contracts\ConnectorInterface;
use Max\Redis\RedisConfig;
use Swoole\Coroutine;

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
