<?php

namespace Max\Di;

use Max\Di\Contracts\AspectInterface;

class AspectManager
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string          $className
     * @param string          $method
     * @param AspectInterface $aspect
     */
    public static function addMethodAspect(string $className, string $method, AspectInterface $aspect)
    {
        self::$container[$className][$method][] = $aspect;
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return array
     */
    public static function getMethodAspects(string $className, string $method): array
    {
        return self::$container[$className][$method] ?? [];
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return self::$container;
    }
}