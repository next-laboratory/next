<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session\Handlers;

use Max\Redis\Redis;
use Max\Session\Exceptions\SessionException;
use Max\Utils\Traits\AutoFillProperties;
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{
    use AutoFillProperties;

    protected Redis $handler;

    protected string $connection;

    protected int $expire = 3600;

    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        if (!class_exists('Max\Redis\Redis')) {
            throw new SessionException('You will need to install the Redis package using `composer require max/redis`');
        }
        $connector     = $options['connector'];
        $this->handler = new Redis(new $connector);
    }

    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function destroy(string $id): bool
    {
        return (bool)$this->handler->del($id);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime): int|false
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function read(string $id): string|false
    {
        if ($data = $this->handler->get($id)) {
            return (string)$data;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function write(string $id, string $data): bool
    {
        return (bool)$this->handler->set($id, $data, $this->expire);
    }
}
