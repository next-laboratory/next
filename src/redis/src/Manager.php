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

namespace Max\Redis;

use ArrayObject;
use InvalidArgumentException;
use Max\Config\Contracts\ConfigInterface;
use Max\Pool\Contracts\PoolInterface;
use Max\Pool\PoolManager;

/**
 * @mixin Redis
 */
class Manager
{
    /**
     * @var string|mixed
     */
    protected string $defaultConnection;

    /**
     * @var ArrayObject
     */
    protected ArrayObject $connections;

    /**
     * @var array|mixed
     */
    protected array $config = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $config                  = $config->get('redis');
        $this->defaultConnection = $config['default'];
        $this->config            = $config['connections'] ?? [];
    }

    /**
     * @param string|null $name
     *
     * @return PoolInterface
     */
    public function connection(?string $name = null): PoolInterface
    {
        $name ??= 'redis.' . $this->defaultConnection;
        if (!PoolManager::has($name)) {
            if (!isset($this->config[$name])) {
                throw new InvalidArgumentException('没有相关数据库连接');
            }
            $config    = $this->config[$name];
            $connector = $config['connector'];
            $options   = $config['options'];
            PoolManager::set($name, new $connector(new RedisConfig($options)));
        }

        return PoolManager::get($name);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->connection($this->defaultConnection)->get()->{$name}(...$arguments);
    }
}
