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

namespace Max\Session\Handlers;

use Max\Container\Exceptions\NotFoundException;
use Max\Utils\Traits\AutoFillProperties;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;
use SessionHandlerInterface;

class Cache implements SessionHandlerInterface
{
    use AutoFillProperties;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $handler;

    /**
     * @var int
     */
    protected int $ttl = 3600;

    /**
     * @param array $options
     *
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function __construct(array $options = [])
    {
        $this->fillProperties($options);
        $this->handler = make(CacheInterface::class);
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close()
    {
        return true;
    }

    /**
     * @param $id
     *
     * @return void
     * @throws InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function destroy($id)
    {
        $this->handler->delete($id);
    }

    /**
     * @param $maxLifeTime
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function gc($maxLifeTime)
    {
        return true;
    }

    /**
     * @param $path
     * @param $name
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open($path, $name)
    {
        return true;
    }

    /**
     * @param $id
     *
     * @return array|false|mixed|string
     * @throws InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function read($id)
    {
        return $this->handler->get($id, []) ?: [];
    }

    /**
     * @param $id
     * @param $data
     *
     * @return void
     * @throws InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function write($id, $data)
    {
        $this->handler->set($id, $data, $this->ttl);
    }
}
