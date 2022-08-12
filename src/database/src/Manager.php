<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use InvalidArgumentException;
use Max\Config\Contract\ConfigInterface;
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

    protected array $connections = [];

    /**
     * @var array|mixed
     */
    protected array $config = [];

    public function __construct(
        ConfigInterface $config,
        protected ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $config                  = $config->get('database');
        $this->defaultConnection = $config['default'];
        $this->config            = $config['connections'] ?? [];
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->connection($this->defaultConnection)->{$name}(...$arguments);
    }

    public function connection(?string $name = null): Query
    {
        $name ??= $this->defaultConnection;
        if (!isset($this->connections[$name])) {
            if (!isset($this->config[$name])) {
                throw new InvalidArgumentException('没有相关数据库连接');
            }
            $config                   = $this->config[$name];
            $connector                = $config['connector'];
            $options                  = $config['options'];
            $options['name']          = $name;
            $this->connections[$name] = new $connector(new DatabaseConfig($options));
        }
        return new Query($this->connections[$name], $this->eventDispatcher);
    }
}
