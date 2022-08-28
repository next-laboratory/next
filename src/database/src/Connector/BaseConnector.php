<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Connector;

use Max\Database\Contract\ConnectorInterface;
use Max\Database\DBConfig;
use PDO;

class BaseConnector implements ConnectorInterface
{
    public function __construct(
        protected DBConfig $config
    ) {
    }

    /**
     * @return PDO
     */
    public function get()
    {
        return new PDO(
            $this->config->getDsn(),
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getOptions()
        );
    }

    public function release($connection)
    {
    }
}
