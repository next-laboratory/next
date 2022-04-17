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

namespace Max\Di;

class AnnotationManager
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $className
     * @param string $property
     * @param object $attribute
     *
     * @return void
     */
    public static function annotationProperty(string $className, string $property, object $attribute): void
    {
        self::$container['property'][$className][$property][] = $attribute;
    }

    /**
     * @param string $className
     * @param object $attribute
     *
     * @return void
     */
    public static function annotationClass(string $className, object $attribute): void
    {
        self::$container['class'][$className][] = $attribute;
    }

    /**
     * @param string $className
     * @param string $method
     * @param object $attribute
     *
     * @return void
     */
    public static function annotationMethod(string $className, string $method, object $attribute): void
    {
        self::$container['method'][$className][$method][] = $attribute;
    }

    /**
     * @param string $className
     * @param string $property
     *
     * @return array
     */
    public static function getPropertyAnnotations(string $className, string $property): array
    {
        return self::$container['property'][$className][$property] ?? [];
    }

    /**
     * @param string $className
     *
     * @return array
     */
    public static function getClassAnnotations(string $className): array
    {
        return self::$container['class'][$className] ?? [];
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return array
     */
    public static function getMethodAnnotations(string $className, string $method): array
    {
        return self::getMethodsAnnotations()[$className][$method] ?? [];
    }

    /**
     * @param string|null $className
     *
     * @return array
     */
    public static function getMethodsAnnotations(?string $className = null): array
    {
        if (isset($className)) {
            return self::$container['method'][$className] ?? [];
        }
        return self::$container['method'] ?? [];
    }

    /**
     * @param string|null $className
     *
     * @return array
     */
    public static function getPropertiesAnnotations(?string $className = null): array
    {
        if (isset($className)) {
            return self::$container['property'][$className] ?? [];
        }
        return self::$container['property'] ?? [];
    }
}
