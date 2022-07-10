<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop\Collectors;

use Max\Aop\Contracts\PropertyAttribute;

class PropertyAttributeCollector extends AbstractCollector
{
    protected static array $container = [];

    /**
     * 收集属性注解.
     */
    public static function collectProperty(string $class, string $property, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container[$class][$property][] = $attribute;
        }
    }

    /**
     * 返回含有属性的类的所有属性和注解.
     */
    public static function getClassPropertyAttributes(string $class): array
    {
        return self::$container[$class] ?? [];
    }

    /**
     * 返回某一个类的某属性的注解.
     *
     * @return PropertyAttribute[]
     */
    public static function getPropertyAttribute(string $class, string $property): array
    {
        return self::$container[$class][$property] ?? [];
    }

    /**
     * 返回收集过的类.
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * 是否可以被收集.
     */
    protected static function isValid(object $attribute): bool
    {
        return $attribute instanceof PropertyAttribute;
    }
}
