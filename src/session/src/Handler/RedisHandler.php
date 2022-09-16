<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session\Handler;

use Max\Redis\Redis;
use Max\Utils\Traits\AutoFillProperties;
use RedisException;
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{
    use AutoFillProperties;

    protected Redis $handler;

    protected string $connector;

    protected string $prefix = 'PHP_SESS';

    /**
     * @var string 主机
     */
    protected string $host = '127.0.0.1';

    /**
     * @var int 端口
     */
    protected int $port = 6379;

    /**
     * @var int 过期时间
     */
    protected int    $expire   = 3600;
    protected int    $database = 0;
    protected string $password = '';

    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        $this->handler = new Redis(new $this->connector($this->host, $this->port));
    }

    /**
     * {@inheritDoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(string $id): bool
    {
        try {
            return (bool)$this->handler->del($id);
        } catch (RedisException) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read(string $id): string|false
    {
        try {
            if ($data = $this->handler->get($this->normalizeId($id))) {
                return (string)$data;
            }
            return false;
        } catch (RedisException) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $id, string $data): bool
    {
        try {
            return (bool)$this->handler->set($this->normalizeId($id), $data, $this->expire);
        } catch (RedisException) {
            return false;
        }
    }

    protected function normalizeId(string $id): string
    {
        return $this->prefix . $id;
    }
}
