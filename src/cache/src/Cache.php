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

namespace Max\Cache;

use Closure;
use Max\Config\Repository;
use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException;

class Cache implements CacheInterface
{
    /**
     * 所有缓存句柄
     *
     * @var CacheInterface[]
     */
    protected static array $handlers = [];

    /**
     * 当前缓存句柄
     *
     * @var CacheInterface
     */
    protected CacheInterface $handler;

    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * @param Repository $repository
     * @return Cache|static
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public static function __new(Repository $repository): Cache|static
    {
        $cache = new static($repository->get('cache'));
        $cache->withHandler('default');
        return $cache;
    }

    /**
     * @param string $name
     *
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    protected function withHandler(string $name)
    {
        $name = strtolower($name);
        if ('default' === $name || !isset($this->config['stores'][$name])) {
            $name = $this->config['default'];
        }
        if (!isset($this->handlers[$name])) {
            $config = $this->config['stores'][$name];
            $handler = $config['handler'];
            if (class_exists('Max\Di\Context')) {
                static::$handlers[$name] = Context::getContainer()->make($handler, [$config['options']]);
            } else {
                static::$handlers[$name] = new $handler($config['options']);
            }
        }
        $this->handler = static::$handlers[$name];
    }

    /**
     * @param string $name
     *
     * @return CacheInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function store(string $name = 'default'): CacheInterface
    {
        $new = clone $this;
        $new->withHandler($name);
        return $new;
    }

    /**
     * 记住缓存并返回
     *
     * @param          $key
     * @param Closure $callback
     * @param int|null $ttl
     *
     * @return mixed
     */
    public function remember($key, Closure $callback, ?int $ttl = null): mixed
    {
        return $this->handler->remember($key, $callback, $ttl);
    }

    /**
     * 自增
     *
     * @param     $key
     * @param int $step
     *
     * @return bool
     */
    public function incr($key, int $step = 1): bool
    {
        return (bool)$this->handler->incr($key, $step);
    }

    /**
     * 自减去
     *
     * @param     $key
     * @param int $step
     *
     * @return bool
     */
    public function decr($key, int $step = 1): bool
    {
        return (bool)$this->handler->decr($key, $step);
    }

    /**
     * 取出并删除
     *
     * @param $key
     *
     * @return mixed
     */
    public function pull($key): mixed
    {
        return $this->handler->pull($key);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $this->handler->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->handler->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        return $this->handler->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->handler->clear();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->handler->getMultiple($keys, $default);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->handler->setMultiple($values, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        return $this->handler->deleteMultiple($keys);
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->handler->has($key);
    }
}
