<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Connector;

use RuntimeException;
use SplQueue;

class BasePoolConnector extends BaseConnector
{
    protected SplQueue $splQueue;

    protected int $num = 0;

    public function __construct(
        string $driver = 'mysql',
        string $host = '127.0.0.1',
        int $port = 3306,
        string $database = '',
        protected string $user = 'root',
        protected string $password = '',
        protected array $options = [],
        protected string $unixSocket = '',
        string $DSN = '',
        protected int $poolSize = 16,
    ) {
        parent::__construct($driver, $host, $port, $database, $this->user, $this->password, $this->options, $this->unixSocket, $DSN);
        $this->splQueue = new SplQueue();
    }

    public function get()
    {
        $isMaximum = $this->num >= $this->poolSize;
        if ($this->isEmpty() && $isMaximum) {
            throw new RuntimeException('Too many connections');
        }
        if (! $isMaximum) {
            $this->splQueue->push($this->newConnection());
            ++$this->num;
        }
        return $this->splQueue->shift();
    }

    public function release($connection)
    {
        if (is_null($connection)) {
            --$this->num;
        } elseif (! $this->isFull()) {
            $this->splQueue->push($connection);
        }
    }

    protected function isFull(): bool
    {
        return $this->splQueue->count() >= $this->poolSize;
    }

    protected function isEmpty(): bool
    {
        return $this->splQueue->isEmpty();
    }
}
