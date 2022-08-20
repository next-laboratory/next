<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Connector;

use ArrayObject;
use Max\Database\Contract\ConnectorInterface;
use Max\Database\DatabaseConfig;
use PDO;

class BaseConnector implements ConnectorInterface
{
    protected ArrayObject $pool;

    public function __construct(
        protected DatabaseConfig $config
    ) {
        $this->pool = new ArrayObject();
    }

    /**
     * @return PDO
     */
    public function get()
    {
        $name = $this->config->getName();
        if (! $this->pool->offsetExists($name)) {
            $this->pool->offsetSet($name, $this->create());
        }
        return $this->pool->offsetGet($name);
    }

    protected function create()
    {
        $PDO = new PDO(
            $this->config->getDsn(),
            $this->config->getUser(),
            $this->config->getPassword(),
            $this->config->getOptions()
        );
        if ($PDO->query('SELECT 1')) {
            return $PDO;
        }
        $this->pool->offsetUnset($this->config->getName());
    }
}
