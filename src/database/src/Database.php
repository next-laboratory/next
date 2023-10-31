<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Database;

use Next\Database\Contract\ConfigInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class Database
{
    public function __construct(
        protected ConfigInterface $config,
        protected ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function query(): Query
    {
        return new Query(
            new \PDO(
                $this->config->getDSN(),
                $this->config->getUser(),
                $this->config->getPassword(),
                $this->config->getOptions()
            ),
            $this->eventDispatcher
        );
    }
}
