<?php

namespace Max\Redis;

use ArrayObject;
use Max\Config\Contracts\ConfigInterface;

/**
 * @mixin \Redis
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
        $this->connections       = new ArrayObject();
        $this->config            = $config['connections'] ?? [];
    }

    /**
     * @param string|null $name
     *
     * @return \Redis
     */
    public function connection(?string $name = null)
    {
        $name ??= $this->defaultConnection;
        if (!$this->connections->offsetExists($name)) {
            if (!isset($this->config[$name])) {
                throw new \InvalidArgumentException('没有相关数据库连接');
            }
            $config          = $this->config[$name];
            $connector       = $config['connector'];
            $options         = $config['options'];
            $options['name'] = $name;
            $this->connections->offsetSet($name, new $connector(new RedisConfig($options)));
        }
        return $this->connections->offsetGet($name)->get();
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->connection($this->defaultConnection)->{$name}(...$arguments);
    }
}
