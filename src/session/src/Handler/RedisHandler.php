<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Session\Handler;

class RedisHandler implements \SessionHandlerInterface
{
    protected \Redis $redis;

    public function __construct(
        protected string $host = '127.0.0.1',
        protected int $port = 6379,
        protected float $timeout = 0,
        protected ?string $persistentId = null,
        protected int $retryInterval = 0,
        protected float $readTimeout = 0,
        protected array $context = [],
        protected string $password = '',
        protected int $database = 0,
        protected string $sessionPrefix = '',
        protected int $sessionTTL = 3600,
    ) {}

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        try {
            return (bool) $this->redis->del($id);
        } catch (\RedisException) {
            return false;
        }
    }

    public function gc(int $max_lifetime): false|int
    {
        return 1;
    }

    /**
     * @throws \RedisException
     */
    public function open(string $path, string $name): bool
    {
        $this->redis = new \Redis();
        if ($this->redis->connect(
            $this->host,
            $this->port,
            $this->timeout,
            $this->persistentId,
            $this->retryInterval,
            $this->readTimeout,
            $this->context
        )) {
            $this->redis->select($this->database);
            if ($this->password) {
                $this->redis->auth($this->password);
            }
        }

        return false;
    }

    public function read(string $id): false|string
    {
        try {
            if ($data = $this->redis->get($this->normalizeId($id))) {
                return (string) $data;
            }
            return false;
        } catch (\RedisException) {
            return false;
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            return (bool) $this->redis->set($this->normalizeId($id), $data, $this->sessionTTL);
        } catch (\RedisException) {
            return false;
        }
    }

    protected function normalizeId(string $id): string
    {
        $key = 'session:' . $id;
        if ($this->sessionPrefix) {
            $key = $this->sessionPrefix . ':' . $key;
        }

        return $key;
    }
}
