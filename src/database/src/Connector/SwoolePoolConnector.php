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
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;

class SwoolePoolConnector implements ConnectorInterface
{
    protected PDOPool $PDOPool;

    public function __construct(
        string $driver = 'mysql',
        string $host = '127.0.0.1',
        int $port = 3306,
        string $database = '',
        string $user = 'root',
        string $password = '',
        array $options = [],
        ?string $unixSocket = null,
        int $poolSize = 16,
    ) {
        $PDOConfig     = (new PDOConfig())
            ->withDriver($driver)
            ->withHost($host)
            ->withUnixSocket($unixSocket)
            ->withPort($port)
            ->withDbname($database)
            ->withUsername($user)
            ->withPassword($password)
            ->withOptions($options);
        $this->PDOPool = new PDOPool($PDOConfig, $poolSize);
        $this->PDOPool->fill();
    }

    public function get()
    {
        return $this->PDOPool->get();
    }

    public function release($connection)
    {
        $this->PDOPool->put($connection);
    }
}
