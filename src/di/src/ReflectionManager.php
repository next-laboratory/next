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

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;

final class ReflectionManager
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $className
     *
     * @return ReflectionClass
     */
    public static function reflectClass(string $className): ReflectionClass
    {
        if (!isset(self::$container['class'][$className])) {
            if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            self::$container['class'][$className] = new ReflectionClass($className);
        }
        return self::$container['class'][$className];
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function reflectMethod(string $className, string $method): ReflectionMethod
    {
        $key = $className . '::' . $method;
        if (!isset(self::$container['method'][$key])) {
            // TODO check interface_exist
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            self::$container['method'][$key] = self::reflectClass($className)->getMethod($method);
        }
        return self::$container['method'][$key];
    }

    /**
     * @param string $className
     * @param string $property
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function reflectProperty(string $className, string $property): ReflectionProperty
    {
        $key = $className . '::' . $property;
        if (!isset(self::$container['property'][$key])) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            self::$container['property'][$key] = self::reflectClass($className)->getProperty($property);
        }
        return self::$container['property'][$key];
    }

    /**
     * @param string $className
     * @param        $filter
     *
     * @return ReflectionProperty[]
     */
    public static function reflectProperties(string $className, $filter = null): array
    {
        return self::reflectClass($className)->getProperties($filter);
    }

    /**
     * @param string $className
     *
     * @return mixed
     */
    public static function reflectPropertyNames(string $className): mixed
    {
        $key = $className;
        if (!isset(self::$container['property_names'][$key])) {
            if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            self::$container['property_names'][$key] = value(fn($className) => array_map(
                fn($property) => $property->getName(), self::reflectProperties($className))
            );
        }
        return self::$container['property_names'][$key];
    }

    /**
     * @param string|null $key
     *
     * @return void
     */
    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            self::$container = [];
        }
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return mixed|null
     */
    public static function getPropertyDefaultValue(ReflectionProperty $property): mixed
    {
        return method_exists($property, 'getDefaultValue')
            ? $property->getDefaultValue()
            : $property->getDeclaringClass()->getDefaultProperties()[$property->getName()] ?? null;
    }

    /**
     * @param string|Closure $function
     *
     * @return ReflectionFunction
     * @throws ReflectionException
     */
    public static function reflectFunction(string|Closure $function): ReflectionFunction
    {
        return new ReflectionFunction($function);
    }
}
