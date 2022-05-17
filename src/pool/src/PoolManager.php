<?php

namespace Max\Pool;

use ArrayObject;
use Max\Pool\Contracts\PoolInterface;
use Max\Pool\Exceptions\PoolException;

class PoolManager
{
    protected static ArrayObject $pool;

    protected static bool $initialized = false;

    public static function init(): void
    {
        if (!self::$initialized) {
            self::$pool        = new ArrayObject();
            self::$initialized = true;
        }
    }

    public static function set(string $name, PoolInterface $pool): void
    {
        self::$pool->offsetSet($name, $pool);
    }

    public static function add(string $name, PoolInterface $pool): void
    {
        self::has($name) ? throw new PoolException(sprintf('Pool %s is already exist.', $name)) : self::set($pool);
    }

    public static function get(string $name): PoolInterface
    {
        return self::has($name) ?
            self::$pool->offsetGet($name) :
            throw new PoolException(sprintf('Pool %s does not exist.', $name));
    }

    public static function has(string $name): bool
    {
        return self::$initialized && self::$pool->offsetExists($name);
    }

    public static function clear(string $name): void
    {
    }

    public static function clean(): void
    {
        self::$pool        = new ArrayObject();
        self::$initialized = false;
    }

    private function __construct()
    {
    }

    private function __clone(): void
    {
    }
}
