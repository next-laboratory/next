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

namespace Max\Di\Container;

use Exception;
use Max\Di\Exceptions\NotFoundException;
use Max\Di\ReflectionManager;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;
use ReflectionProperty;
use function is_object;

trait PropertyModifier
{
    /**
     * 设置属性[未测试]
     *
     * @param        $object
     * @param string $property
     * @param null   $value
     *
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function setProperty($object, string $property, $value = null)
    {
        $reflectionClass = ReflectionManager::reflectClass($object);
        if ($reflectionClass->hasProperty($property)) {
            $property = $reflectionClass->getProperty($property);
            $this->setValue(is_object($object) ? $object : $this->make($object), $property, $value);
        }
    }

    /**
     * 设置权限
     *
     * @param ReflectionProperty $reflectionProperty
     *
     * @return ReflectionProperty
     */
    protected function setAccessible(ReflectionProperty $reflectionProperty): ReflectionProperty
    {
        if (!$reflectionProperty->isPublic()) {
            $reflectionProperty->setAccessible(true);
        }
        return $reflectionProperty;
    }

    /**
     * 获取一个属性
     *
     * @param $object
     * @param $property
     *
     * @return mixed
     */
    public function getProperty($object, $property): mixed
    {
        try {
            $property = ReflectionManager::reflectProperty($object, $property);
            $this->setAccessible($property);
            if ($property->isStatic()) {
                return $property->getValue();
            }
            $object = is_object($object) ? $object : $this->resolve($object);
            return $property->getValue($object);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param object              $object
     * @param ReflectionProperty  $reflectionProperty
     * @param                     $value
     */
    public function setValue(object $object, ReflectionProperty $reflectionProperty, $value)
    {
        $this->setAccessible($reflectionProperty)->setValue($object, $value);
    }
}
