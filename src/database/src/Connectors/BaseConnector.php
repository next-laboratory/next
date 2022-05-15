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

use Max\Database\Contracts\ConnectorInterface;
use Max\Database\DatabaseConfig;
use PDO;

class BaseConnector implements ConnectorInterface
{
    /**
     * @param DatabaseConfig $config
     */
    public function __construct(protected DatabaseConfig $config)
    {
    }

    /**
     * @return PDO
     */
    public function get()
    {
        return new PDO($this->config->getDsn(), $this->config->getUser(), $this->config->getPassword(), $this->config->getOptions());
    }
}
