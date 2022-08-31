<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Closure;
use Exception;
use Max\Database\Contract\ConnectorInterface;
use Max\Database\Eloquent\Model;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

class Manager
{
    protected string $default     = 'mysql';

    protected array  $connections = [];

    protected array  $config      = [];

    public function __construct(
        protected ?EventDispatcherInterface $eventDispatcher = null
    ) {
        Model::setManager($this);
    }

    public function setDefault(string $name): void
    {
        $this->default = $name;
    }

    public function addConnection(string $name, DBConfig $config): void
    {
        $connector = new ($config->getConnector())($config);
        if (! $connector instanceof ConnectorInterface) {
            throw new RuntimeException();
        }
        $this->connections[$name] = $connector;
    }

    public function query(string $name = ''): Query
    {
        $name = $name ?: $this->default;
        if (! isset($this->connections[$name])) {
            throw new RuntimeException('没有相关数据库连接');
        }

        return new Query($this->connections[$name], $this->eventDispatcher);
    }

    /**
     * @throws Exception
     */
    public function extend(string $name, Closure $resolver): void
    {
        $connector = ($resolver)($this);
        if (! $connector instanceof ConnectorInterface) {
            throw new Exception('The resolver should return an instance of ConnectorInterface');
        }
        $this->connections[$name] = $connector;
    }
}
