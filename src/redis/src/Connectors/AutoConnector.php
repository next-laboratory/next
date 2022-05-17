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

use Max\Pool\Contracts\Poolable;
use Max\Pool\Contracts\PoolInterface;
use Max\Redis\RedisConfig;
use Swoole\Coroutine;

class AutoConnector implements PoolInterface
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
    public function get(): Poolable
    {
        $type = class_exists(Coroutine::class) && Coroutine::getCid() > 0 ? 'pool' : 'base';
        if (!isset($this->container[$type])) {
            $connector              = $this->connectors[$type];
            $this->container[$type] = new $connector($this->config);
        }
        return $this->container[$type]->get();
    }

    public function open()
    {
        // TODO: Implement open() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function gc()
    {
        // TODO: Implement gc() method.
    }

    public function release(Poolable $poolable)
    {
        // TODO: Implement release() method.
    }

    public function put(Poolable $poolable)
    {
        // TODO: Implement put() method.
    }
}
