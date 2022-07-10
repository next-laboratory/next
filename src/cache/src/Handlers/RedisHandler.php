<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache\Handlers;

use Max\Redis\RedisManager;
use Max\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RedisHandler extends CacheHandler
{
    use AutoFillProperties;

    protected string $connection;

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(array $config)
    {
        $this->fillProperties($config);
        /** @var RedisManager $manager */
        $manager       = make(RedisManager::class);
        $this->handler = $manager->connection($this->connection);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return (bool) $this->handler->del($key);
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return (bool) $this->handler->exists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->handler->flushAll();
    }
}
