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

use Max\Di\Contracts\PropertyAttribute;

class PropertyAttributeCollector extends AbstractCollector
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $class
     * @param string $property
     * @param object $attribute
     *
     * @return void
     */
    public static function collectProperty(string $class, string $property, object $attribute): void
    {
        self::$container[$class][$property][] = $attribute;
    }

    /**
     * @param string $class
     *
     * @return PropertyAttribute[]
     */
    public static function getClassPropertyAttributes(string $class): array
    {
        return self::$container[$class] ?? [];
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return PropertyAttribute[]
     */
    public static function getPropertyAttribute(string $class, string $property): array
    {
        return self::$container[$class][$property] ?? [];
    }

    /**
     * @return array
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * @param object $attribute
     *
     * @return bool
     */
    protected static function isValid(object $attribute): bool
    {
        return $attribute instanceof PropertyAttribute;
    }
}
