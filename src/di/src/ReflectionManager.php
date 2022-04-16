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

class ReflectionManager
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $className
     *
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public static function reflectClass(string $className): ReflectionClass
    {
        if (!isset(static::$container['class'][$className])) {
            if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['class'][$className] = new ReflectionClass($className);
        }
        return static::$container['class'][$className];
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
        if (!isset(static::$container['method'][$key])) {
            // TODO check interface_exist
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['method'][$key] = static::reflectClass($className)->getMethod($method);
        }
        return static::$container['method'][$key];
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
        if (!isset(static::$container['property'][$key])) {
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property'][$key] = static::reflectClass($className)->getProperty($property);
        }
        return static::$container['property'][$key];
    }

    /**
     * @param string $className
     * @param        $filter
     *
     * @return ReflectionProperty[]
     * @throws ReflectionException
     */
    public static function reflectProperties(string $className, $filter = null): array
    {
        return static::reflectClass($className)->getProperties($filter);
    }

    /**
     * @param string $className
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function reflectPropertyNames(string $className): mixed
    {
        $key = $className;
        if (!isset(static::$container['property_names'][$key])) {
            if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['property_names'][$key] = value(fn($className) => array_map(
                fn($property) => $property->getName(), static::reflectProperties($className))
            );
        }
        return static::$container['property_names'][$key];
    }

    /**
     * @param string|null $key
     *
     * @return void
     */
    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            static::$container = [];
        }
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return mixed|null
     */
    public static function getPropertyDefaultValue(ReflectionProperty $property)
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
