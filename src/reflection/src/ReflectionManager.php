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
    protected static array $reflectionMethods    = [];
    protected static array $reflectionProperties = [];

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
        if (!isset(self::$reflectionMethods[$class][$method])) {
            self::$reflectionMethods[$class][$method] = self::reflectClass($class)->getMethod($method);
        }
        return self::$reflectionMethods[$class][$method];
    }

    /**
     * @return ReflectionMethod[]
     * @throws ReflectionException
     */
    public static function reflectMethods(string $class, ?int $filter = null): array
    {
        return self::reflectClass($class)->getMethods($filter);
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectProperty(string $class, string $property): ReflectionProperty
    {
        if (!isset(self::$reflectionProperties[$class][$property])) {
            self::$reflectionProperties[$class][$property] = self::reflectClass($class)->getProperty($property);
        }
        return self::$reflectionProperties[$class][$property];
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
                self::reflectMethodParameters($class, $method)
            );
        }
        return self::$methodParameterNames[$key];
    }

    /**
     * @return ReflectionParameter[]
     * @throws ReflectionException
     */
    public static function reflectMethodParameters(string $class, string $method): array
    {
        return self::reflectMethod($class, $method)->getParameters();
    }

    /**
     * @throws ReflectionException
     */
    public static function reflectPropertyNames(string $class)
    {
        if (!isset(self::$propertiesNames[$class])) {
            self::$propertiesNames[$class] = value(fn($class) => array_map(
                fn($property) => $property->getName(), self::reflectProperties($class))
            );
        }
        return self::$propertiesNames[$class];
    }

    public static function getPropertyDefaultValue(ReflectionProperty $property)
    {
        return method_exists($property, 'getDefaultValue')
            ? $property->getDefaultValue()
            : $property->getDeclaringClass()->getDefaultProperties()[$property->getName()] ?? null;
    }
}
