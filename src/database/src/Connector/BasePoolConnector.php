<?php

namespace Max\Database\Connector;

use RuntimeException;
use SplQueue;

class BasePoolConnector extends BaseConnector
{
    protected SplQueue $splQueue;
    protected int      $num = 0;

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
        if ($this->isEmpty() && $this->num >= $this->poolSize) {
            throw new RuntimeException('Too many connections');
        }
        if ($this->num < $this->poolSize) {
            $this->splQueue->push($this->newConnection());
            $this->num++;
        }
        return $this->splQueue->shift();
    }

    public function release($connection)
    {
        if (is_null($connection)) {
            $this->num--;
        } else if (!$this->isFull()) {
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
