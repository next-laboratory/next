<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Database\Connectors;

use ArrayObject;
use Max\Database\Contracts\ConnectorInterface;
use Max\Database\DatabaseConfig;
use PDO;

class BaseConnector implements ConnectorInterface
{
    /**
     * @var ArrayObject
     */
    protected ArrayObject $pool;

    /**
     * @param DatabaseConfig $config
     */
    public function __construct(protected DatabaseConfig $config)
    {
        $this->pool = new ArrayObject();
    }

    /**
     * @return PDO
     */
    public function get()
    {
        $name = $this->config->getName();
        if (!$this->pool->offsetExists($name)) {
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
