<?php

namespace Max\Session\Handlers;

use Max\Redis\RedisManager;
use Max\Redis\Redis;
use Max\Session\Exceptions\SessionException;
use Max\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use SessionHandlerInterface;

class RedisHandler implements SessionHandlerInterface
{
    use AutoFillProperties;

    /**
     * @var Redis
     */
    protected Redis $handler;

    /**
     * @var string
     */
    protected string $connection;

    /**
     * @var int
     */
    protected int $expire = 3600;

    /**
     * @param array $options
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        if (!class_exists('Max\Redis\RedisManager')) {
            throw new SessionException('You will need to install the Redis package using `composer require max/redis`');
        }
        /** @var RedisManager $manager */
        $manager       = make(RedisManager::class);
        $this->handler = $manager->connection($this->connection);
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     *
     * @return bool|void
     */
    #[\ReturnTypeWillChange]
    public function destroy(string $id)
    {
        $this->handler->del($id);
    }

    /**
     * @param int $max_lifetime
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime)
    {
        return true;
    }

    /**
     * @param string $path
     * @param string $name
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open(string $path, string $name)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function read(string $id)
    {
        return $this->handler->get($id);
    }

    /**
     * @param string $id
     * @param string $data
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function write(string $id, string $data)
    {
        $this->handler->set($id, $data, $this->expire);
    }
}
