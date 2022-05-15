<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Cache\Handlers;

use Max\Redis\Manager;
use Max\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class RedisHandler extends CacheHandler
{
    use AutoFillProperties;

    /**
     * @var string
     */
    protected string $connection;

    /**
     * @param array $config
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(array $config)
    {
        $this->fillProperties($config);
        /** @var Manager $manager */
        $manager       = make(Manager::class);
        $this->handler = $manager->connection($this->connection);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return (bool)$this->handler->del($key);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return (bool)$this->handler->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->handler->flushAll();
    }
}
