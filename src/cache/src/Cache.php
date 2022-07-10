<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache;

use ArrayObject;
use InvalidArgumentException;
use Max\Config\Contracts\ConfigInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @mixin CacheInterface
 */
class Cache implements CacheInterface
{
    /**
     * 当前缓存句柄.
     */
    protected CacheInterface $handler;

    /**
     * @var mixed|string
     */
    protected string $defaultStore;

    protected ArrayObject $stores;

    /**
     * @var array|mixed
     */
    protected array $config = [];

    public function __construct(ConfigInterface $config)
    {
        $config             = $config->get('cache');
        $this->defaultStore = $config['default'];
        $this->config       = $config['stores'];
        $this->stores       = new ArrayObject();
    }

    /**
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->store()->{$name}(...$arguments);
    }

    /**
     * @return false|mixed
     */
    public function store(?string $name = null)
    {
        $name ??= $this->defaultStore;
        if (! $this->stores->offsetExists($name)) {
            if (! isset($this->config[$name])) {
                throw new InvalidArgumentException('配置不正确');
            }
            $config  = $this->config[$name];
            $handler = $config['handler'];
            $this->stores->offsetSet($name, new ($handler)($config['options']));
        }
        return $this->stores->offsetGet($name);
    }

    /**
     * @param $key
     * @param $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $key
     * @param $value
     * @param $ttl
     *
     * @return bool|mixed
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function delete($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool|mixed
     */
    public function clear()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $keys
     * @param $default
     *
     * @return iterable|mixed
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $values
     * @param $ttl
     *
     * @return bool|mixed
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $keys
     *
     * @return bool|mixed
     */
    public function deleteMultiple($keys)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     */
    public function has($key)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }
}
