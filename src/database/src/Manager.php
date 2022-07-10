<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use ArrayObject;
use InvalidArgumentException;
use Max\Config\Contracts\ConfigInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @mixin Query
 */
class Manager
{
    /**
     * @var mixed|string
     */
    protected string $defaultConnection;

    protected ArrayObject $connections;

    /**
     * @var array|mixed
     */
    protected array $config = [];

    /**
     * @param null|EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ConfigInterface $config, protected ?EventDispatcherInterface $eventDispatcher = null)
    {
        $config                  = $config->get('database');
        $this->defaultConnection = $config['default'];
        $this->connections       = new ArrayObject();
        $this->config            = $config['connections'] ?? [];
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->connection($this->defaultConnection)->{$name}(...$arguments);
    }

    /**
     * @return Query
     */
    public function connection(?string $name = null)
    {
        $name ??= $this->defaultConnection;
        if (! $this->connections->offsetExists($name)) {
            if (! isset($this->config[$name])) {
                throw new InvalidArgumentException('没有相关数据库连接');
            }
            $config          = $this->config[$name];
            $connector       = $config['connector'];
            $options         = $config['options'];
            $options['name'] = $name;
            $this->connections->offsetSet($name, new $connector(new DatabaseConfig($options)));
        }
        return new Query($this->connections->offsetGet($name), $this->eventDispatcher);
    }
}
