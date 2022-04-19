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

use Max\Di\Exceptions\ContainerException;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionUnionType;
use function array_shift;
use function is_null;

trait DependencyFinder
{
    /**
     * 获取构造函数的参数
     *
     * @param ReflectionClass $reflectionClass
     * @param array           $arguments
     *
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function getConstructorArgs(ReflectionClass $reflectionClass, array $arguments = []): array
    {
        if (null === ($constructor = $reflectionClass->getConstructor())) {
            return $arguments;
        }
        if ($reflectionClass->isInstantiable()) {
            return $this->getFuncArgs($constructor, $arguments);
        }
        throw new ContainerException('Cannot initialize class: ' . $reflectionClass->getName(), 599);
    }

    /**
     * @param ReflectionFunctionAbstract $reflectionMethod 反射方法
     * @param array                      $arguments        参数列表，支持关联数组，会自动按照变量名传入
     *
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function getFuncArgs(ReflectionFunctionAbstract $reflectionMethod, array $arguments = []): array
    {
        $funcArgs = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $funcArgs[] = $arguments[$name];
                unset($arguments[$name]);
            } else {
                $type = $parameter->getType();
                if (is_null($type)
                    || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                    || $type instanceof ReflectionUnionType
                    || ($typeName = $type->getName()) === 'Closure') {
                    $funcArgs[] = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
                } else {
                    // 当接口注入后又传递参数的时候会报错
                    $funcArgs[] = $this->make($typeName);
                }
            }
        }

        return array_map(fn($value) => is_null($value) && !empty($arguments) ? array_shift($arguments) : $value, $funcArgs);
    }
}
