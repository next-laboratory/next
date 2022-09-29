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
use PDO;

use function sprintf;

class BaseConnector implements ConnectorInterface
{
    public function __construct(
        string $driver = 'mysql',
        string $host = '127.0.0.1',
        int $port = 3306,
        string $database = '',
        protected string $user = 'root',
        protected string $password = '',
        protected array $options = [],
        string $unixSocket = '',
        protected string $DSN = '',
    ) {
        if (empty($this->DSN)) {
            $this->DSN = sprintf('%s:host=%s;port=%s;', $driver, $host, $port);
            if (! empty($database)) {
                $this->DSN .= 'dbname=' . $database . ';';
            }
            if (! empty($unixSocket)) {
                $this->DSN .= 'unix_socket=' . $unixSocket . ';';
            }
        }
    }

    public function get()
    {
        return new PDO($this->DSN, $this->user, $this->password, $this->options);
    }

    public function release($connection)
    {
    }
}
