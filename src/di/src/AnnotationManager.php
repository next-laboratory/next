<?php

namespace Max\Di;

use Max\Di\Contracts\ClassAttribute;
use Max\Di\Contracts\MethodAttribute;
use Max\Di\Contracts\PropertyAttribute;

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
    public static function annotationProperty(string $className, string $property, object $attribute)
    {
        self::$container['property'][$className][$property][] = $attribute;
    }

    /**
     * @param string $className
     * @param object $attribute
     *
     * @return void
     */
    public static function annotationClass(string $className, object $attribute)
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
    public static function annotationMethod(string $className, string $method, object $attribute)
    {
        self::$container['method'][$className][$method][] = $attribute;
    }

    /**
     * @param string $className
     * @param string $property
     *
     * @return array|mixed
     */
    public static function getPropertyAnnotations(string $className, string $property)
    {
        return self::$container['property'][$className][$property] ?? [];
    }

    /**
     * @param string $className
     *
     * @return array|mixed
     */
    public static function getClassAnnotations(string $className)
    {
        return self::$container['class'][$className] ?? [];
    }

    /**
     * @param string $className
     * @param string $method
     *
     * @return array|mixed
     */
    public static function getMethodAnnotations(string $className, string $method)
    {
        return self::getMethodsAnnotations()[$className][$method] ?? [];
    }

    public static function getMethodsAnnotations()
    {
        return self::$container['method'] ?? [];
    }

    public static function getPropertiesAnnotations()
    {
        return self::$container['property'] ?? [];
    }
}
