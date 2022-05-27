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

namespace Max\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

final class ReflectionManager
{
    protected static array $reflectionClasses    = [];
    protected static array $methodParameterNames = [];
    protected static array $propertiesNames      = [];

    /**
     * @throws ReflectionException
     */
    public static function reflectClass(string $class): ReflectionClass
    {
        if (!isset(self::$reflectionClasses[$class])) {
            self::$reflectionClasses[$class] = new ReflectionClass($class);
        }
        return self::$reflectionClasses[$class];
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectMethod(string $class, string $method): ReflectionMethod
    {
        return self::reflectClass($class)->getMethod($method);
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectProperty(string $class, string $property): ReflectionProperty
    {
        return self::reflectClass($class)->getProperty($property);
    }

    /**
     * @return ReflectionProperty[]
     * @throws ReflectionException
     */
    public static function reflectProperties(string $class, $filter = null): array
    {
        return self::reflectClass($class)->getProperties($filter);
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectMethodParameterNames(string $class, string $method): array
    {
        $key = $class . '@' . $method;
        if (!isset(self::$methodParameterNames[$key])) {
            self::$methodParameterNames[$key] = array_map(
                fn(ReflectionParameter $reflectionParameter) => $reflectionParameter->getName(),
                self::reflectMethod($class, $method)->getParameters()
            );
        }
        return self::$methodParameterNames[$key];
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectPropertyNames(string $class): mixed
    {
        if (!isset(self::$propertiesNames[$class])) {
            self::$propertiesNames[$class] = value(fn($class) => array_map(
                fn($property) => $property->getName(), self::reflectProperties($class))
            );
        }
        return self::$propertiesNames[$class];
    }

    public static function getPropertyDefaultValue(ReflectionProperty $property): mixed
    {
        return method_exists($property, 'getDefaultValue')
            ? $property->getDefaultValue()
            : $property->getDeclaringClass()->getDefaultProperties()[$property->getName()] ?? null;
    }
}
