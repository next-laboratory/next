<?php

namespace Max\Database;

use ArrayObject;
use Max\Config\Contracts\ConfigInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @mixin Query
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
     * @param ConfigInterface               $config
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(ConfigInterface $config, protected ?EventDispatcherInterface $eventDispatcher = null)
    {
        $config                  = $config->get('database');
        $this->defaultConnection = $config['default'];
        $this->connections       = new ArrayObject();
        $this->config            = $config['connections'] ?? [];
    }

    /**
     * @param string|null $name
     *
     * @return Query
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
            $this->connections->offsetSet($name, new $connector(new DatabaseConfig($options)));
        }
        return new Query($this->connections->offsetGet($name)->get(), $this->eventDispatcher);
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
