<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop\Collector;

use Next\Aop\Attribute\AspectConfig;
use Next\Aop\Contract\AspectInterface;
use Next\Aop\Aop;
use Next\Di\Reflection;
use ReflectionException;

class AspectCollector extends AbstractCollector
{
    protected static array $container = [];

    /**
     * 收集方法切面.
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            self::$container[$class][$method][] = $attribute;
        }
    }

    /**
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            foreach (Reflection::class($class)->getMethods() as $reflectionMethod) {
                if (!$reflectionMethod->isConstructor()) {
                    self::$container[$class][$reflectionMethod->getName()][] = $attribute;
                }
            }
        } elseif ($attribute instanceof AspectConfig) {
            $reflectionClass = Reflection::class($attribute->class);
            $annotation      = new $class(...$attribute->params);
            $methods         = $attribute->methods;
            if ($methods === '*') {
                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    if (!$reflectionMethod->isConstructor()) {
                        self::$container[$attribute->class][$reflectionMethod->getName()][] = $annotation;
                    }
                }
            } else {
                foreach ((array)$methods as $method) {
                    self::$container[$attribute->class][$method][] = $annotation;
                }
            }
            Aop::addClass($attribute->class, $reflectionClass->getFileName());
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
     * @return string[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }
}
