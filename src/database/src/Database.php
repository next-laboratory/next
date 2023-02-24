<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Max\Database\Contract\ConnectorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Database
{
    public function __construct(
        protected ConnectorInterface        $connector,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    )
    {
    }

    public function query(): Query
    {
        return new Query($this->connector, $this->eventDispatcher);
    }

    public function table(string $table, string $alias = ''): QueryBuilder
    {
        return $this->query()->table($table, $alias);
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
