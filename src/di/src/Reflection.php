<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Di;

final class Reflection
{
    private static array $reflectionClasses = [];

    private static array $methodParameterNames = [];

    private static array $propertiesNames = [];

    private static array $reflectionMethods = [];

    private static array $reflectionProperties = [];

    /**
     * @throws \ReflectionException
     */
    public static function class(string $class): \ReflectionClass
    {
        if (! isset(self::$reflectionClasses[$class])) {
            self::$reflectionClasses[$class] = new \ReflectionClass($class);
        }
        return self::$reflectionClasses[$class];
    }

    /**
     * @throws \ReflectionException
     */
    public static function method(string $class, string $method): \ReflectionMethod
    {
        if (! isset(self::$reflectionMethods[$class][$method])) {
            self::$reflectionMethods[$class][$method] = self::class($class)->getMethod($method);
        }
        return self::$reflectionMethods[$class][$method];
    }

    /**
     * @return \ReflectionMethod[]
     * @throws \ReflectionException
     */
    public static function methods(string $class, ?int $filter = null): array
    {
        return self::class($class)->getMethods($filter);
    }

    /**
     * @throws \ReflectionException
     */
    public static function property(string $class, string $property): \ReflectionProperty
    {
        if (! isset(self::$reflectionProperties[$class][$property])) {
            self::$reflectionProperties[$class][$property] = self::class($class)->getProperty($property);
        }
        return self::$reflectionProperties[$class][$property];
    }

    /**
     * @return \ReflectionProperty[]
     * @throws \ReflectionException
     */
    public static function properties(string $class, ?int $filter = null): array
    {
        return self::class($class)->getProperties($filter);
    }

    /**
     * @throws \ReflectionException
     */
    public static function methodParameterNames(string $class, string $method): array
    {
        $key = $class . '@' . $method;
        if (! isset(self::$methodParameterNames[$key])) {
            self::$methodParameterNames[$key] = array_map(
                fn (\ReflectionParameter $reflectionParameter) => $reflectionParameter->getName(),
                self::methodParameters($class, $method)
            );
        }
        return self::$methodParameterNames[$key];
    }

    /**
     * @return \ReflectionParameter[]
     * @throws \ReflectionException
     */
    public static function methodParameters(string $class, string $method): array
    {
        return self::method($class, $method)->getParameters();
    }

    /**
     * @throws \ReflectionException
     */
    public static function propertyNames(string $class)
    {
        if (! isset(self::$propertiesNames[$class])) {
            self::$propertiesNames[$class] = array_map(fn ($property) => $property->getName(), self::properties($class));
        }
        return self::$propertiesNames[$class];
    }

    /**
     * @return null|mixed
     */
    public static function propertyDefaultValue(\ReflectionProperty $property): mixed
    {
        return method_exists($property, 'getDefaultValue')
            ? $property->getDefaultValue()
            : $property->getDeclaringClass()->getDefaultProperties()[$property->getName()] ?? null;
    }

    public static function clear(): void
    {
        self::$propertiesNames      = [];
        self::$reflectionProperties = [];
        self::$reflectionMethods    = [];
        self::$methodParameterNames = [];
        self::$reflectionClasses    = [];
    }
}
