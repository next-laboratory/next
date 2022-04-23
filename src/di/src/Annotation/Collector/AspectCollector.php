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

namespace Max\Di\Annotation\Collector;

use Max\Di\Contracts\AspectInterface;

class AspectCollector extends AbstractCollector
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $class
     * @param string $method
     * @param object $attribute
     *
     * @return void
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container['method'][$class][$method][] = $attribute;
        }
    }

    /**
     * @param string $class
     * @param object $attribute
     *
     * @return void
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container['class'][$class][] = $attribute;
        }
    }

    /**
     * @param string $class
     * @param string $method
     *
     * @return array
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        return self::$container['method'][$class][$method] ?? [];
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public static function getClassAspects(string $class): array
    {
        return self::$container['class'][$class] ?? [];
    }

    /**
     * @return array
     */
    public static function getCollectedClasses(): array
    {
        return array_unique([...array_keys(self::$container['class'] ?? []), ...array_keys(self::$container['method'] ?? [])]);
    }

    /**
     * @param object $attribute
     *
     * @return bool
     */
    public static function isValid(object $attribute): bool
    {
        return $attribute instanceof AspectInterface;
    }
}
