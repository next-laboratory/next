<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop\Collectors;

use Max\Aop\Contracts\AspectInterface;

class AspectCollector extends AbstractCollector
{
    protected static array $container = [];

    /**
     * 收集方法切面.
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container[$class][$method][] = $attribute;
        }
    }

    /**
     * 返回某个类方法的切面.
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        return self::$container[$class][$method] ?? [];
    }

    /**
     * 返回被收集过的类.
     *
     * @return AspectInterface[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * 是否可以被收集.
     */
    public static function isValid(object $attribute): bool
    {
        return $attribute instanceof AspectInterface;
    }
}
