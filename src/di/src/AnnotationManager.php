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

use ReflectionException;

class AnnotationManager
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param      $class
     * @param null $name
     * @param int  $flags
     *
     * @return array
     * @throws ReflectionException
     */
    public static function annotationClass($class, $name = null, int $flags = 0): array
    {
        return static::readAnnotation(ReflectionManager::reflectClass($class), $name, $flags);
    }

    /**
     * @param      $class
     * @param null $method
     * @param null $name
     * @param int  $flags
     *
     * @return array
     * @throws ReflectionException
     */
    public static function annotationMethod($class, $method = null, $name = null, int $flags = 0): array
    {
        return static::readAnnotation(ReflectionManager::reflectMethod($class, $method), $name, $flags);
    }

    /**
     * @param      $reflection
     * @param null $name
     * @param int  $flags
     *
     * @return array
     */
    public static function readAnnotation($reflection, $name = null, int $flags = 0): array
    {
        $annotations = static::get($reflection, $name, $flags);
        $entry       = [];
        foreach ($annotations as $annotation) {
            $entry[] = $annotation->newInstance();
        }
        return $entry;
    }

    /**
     * @param      $reflection
     * @param null $name
     * @param int  $flags
     *
     * @return mixed
     */
    public static function get($reflection, $name = null, int $flags = 0)
    {
        return $reflection->getAttributes($name, $flags);
    }
}
