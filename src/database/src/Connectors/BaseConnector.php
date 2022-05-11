<?php

namespace Max\Database\Connectors;

use Max\Context\Context;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\DatabaseConfig;

class BaseConnector implements ConnectorInterface
{
    /**
     * @param DatabaseConfig $config
     */
    public function __construct(protected DatabaseConfig $config)
    {
    }

    /**
     * @return \PDO
     */
    public function get()
    {
        return new \PDO($this->config->getDsn(), $this->config->getUser(), $this->config->getPassword(), $this->config->getOptions());
    }
}
