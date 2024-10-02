<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\Redis\Connector;

use Next\Redis\Contract\ConnectorInterface;
use Redis;

class BaseConnector implements ConnectorInterface
{
    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 6379,
        protected float $timeout = 0.0,
        protected $reserved = null,
        protected int $retryInterval = 0,
        protected float $readTimeout = 0.0,
        protected string $auth = '',
        protected int $database = 0,
    ) {
    }

    /**
     * @throws \RedisException
     */
    public function get()
    {
        $redis = new Redis();
        $redis->connect(
            $this->host,
            $this->port,
            $this->timeout,
            $this->reserved,
            $this->retryInterval,
            $this->readTimeout
        );
        $redis->select($this->database);
        $this->auth && $redis->auth($this->auth);
        return $redis;
    }

    public function release($connection)
    {
    }
}
