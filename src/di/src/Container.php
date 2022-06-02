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

use BadMethodCallException;
use Closure;
use Max\Di\Exceptions\{ContainerException, NotFoundException};
use Max\Reflection\ReflectionManager;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface};
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use function array_shift;
use function is_null;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    /**
     * 类和标识对应关系
     */
    protected array $bindings = [];

    /**
     * 已经解析实例
     */
    protected array $resolved = [];

    /**
     * 将实例化的类存放到数组中
     *
     * @param string $id       标识
     * @param object $instance 实例
     */
    public function set(string $id, object $instance)
    {
        $this->resolved[$this->getBinding($id)] = $instance;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->resolved[$this->getBinding($id)];
        }
        throw new NotFoundException('No instance found: ' . $id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->resolved[$this->getBinding($id)]);
    }

    /**
     * @param string $id    标识，可以是接口
     * @param string $class 类名
     *
     * @return void
     */
    public function bind(string $id, string $class): void
    {
        $this->bindings[$id] = $class;
    }

    /**
     * @param string $id 标识
     */
    public function unBind(string $id): void
    {
        if ($this->bound($id)) {
            unset($this->bindings[$id]);
        }
    }

    /**
     * @param string $id 标识
     */
    public function bound(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * @param string $id 标识
     */
    public function getBinding(string $id): string
    {
        return $this->bindings[$id] ?? $id;
    }

    /**
     * 注入的外部接口方法
     *
     * @param string $id        标识
     * @param array  $arguments 构造函数参数列表
     *
     * @return object
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function make(string $id, array $arguments = []): object
    {
        if (false === $this->has($id)) {
            $id              = $this->getBinding($id);
            $reflectionClass = ReflectionManager::reflectClass($id);
            if ($reflectionClass->isInterface()) {
                if (!$this->bound($id)) {
                    throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
                }
                // TODO 当绑定的类并没有实现该接口
                $reflectionClass = ReflectionManager::reflectClass($this->getBinding($id));
            }

            $this->set($id, $reflectionClass->newInstanceArgs($this->getConstructorArgs($reflectionClass, $arguments)));
        }
        return $this->get($id);
    }

    /**
     * 注销实例
     *
     * @param string $id
     */
    public function remove(string $id): void
    {
        $id = $this->getBinding($id);
        if ($this->has($id)) {
            unset($this->resolved[$id]);
        }
    }

    /**
     * 调用类的方法
     *
     * @param callable $callable  可调用的类或者实例和方法数组
     * @param array    $arguments 给方法传递的参数
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function call(callable $callable, array $arguments = [])
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return $this->callFunc($callable, $arguments);
        }
        [$objectOrClass, $method] = $callable;
        $isObject         = is_object($objectOrClass);
        $reflectionMethod = ReflectionManager::reflectMethod($isObject ? get_class($objectOrClass) : $this->getBinding($objectOrClass), $method);
        if (false === $reflectionMethod->isAbstract()) {
            if (!$reflectionMethod->isPublic()) {
                $reflectionMethod->setAccessible(true);
            }

            return $reflectionMethod->invokeArgs(
                $reflectionMethod->isStatic() ? null : ($isObject ? $objectOrClass : $this->make($objectOrClass)),
                $this->getFuncArgs($reflectionMethod, $arguments)
            );
        }
        throw new BadMethodCallException('Unable to call method: ' . $method);
    }

    /**
     * 调用闭包
     *
     * @param Closure|string $function  函数
     * @param array          $arguments 参数列表
     *
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function callFunc($function, array $arguments = [])
    {
        $reflectFunction = new ReflectionFunction($function);

        return $reflectFunction->invokeArgs(
            $this->getFuncArgs($reflectFunction, $arguments)
        );
    }

    /**
     * 获取构造函数的参数
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function getConstructorArgs(ReflectionClass $reflectionClass, array $arguments = []): array
    {
        if (is_null($constructor = $reflectionClass->getConstructor())) {
            return $arguments;
        }
        if ($reflectionClass->isInstantiable()) {
            return $this->getFuncArgs($constructor, $arguments);
        }
        throw new ContainerException('Cannot initialize class: ' . $reflectionClass->getName(), 599);
    }

    /**
     * @param ReflectionFunctionAbstract $reflectionFunction 反射方法
     * @param array                      $arguments          参数列表，支持关联数组，会自动按照变量名传入
     *
     * @throws ContainerExceptionInterface|ReflectionException
     */
    public function getFuncArgs(ReflectionFunctionAbstract $reflectionFunction, array $arguments = []): array
    {
        $funcArgs = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $funcArgs[] = $arguments[$name];
                unset($arguments[$name]);
            } else {
                $type = $parameter->getType();
                if (is_null($type)
                    || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                    || (PHP_VERSION_ID >= 80000 && $type instanceof ReflectionUnionType)
                    || ($typeName = $type->getName()) === 'Closure') {
                    $funcArgs[] = $this->getParameterDefaultValue($parameter);
                } else {
                    // 当接口注入后又传递参数的时候会报错
                    $funcArgs[] = $this->make($typeName);
                }
            }
        }

        return array_map(fn($value) => is_null($value) && !empty($arguments) ? array_shift($arguments) : $value, $funcArgs);
    }

    /**
     * @param ReflectionParameter $reflectionParameter
     *
     * @return mixed|null
     * @throws ReflectionException
     */
    protected function getParameterDefaultValue(ReflectionParameter $reflectionParameter)
    {
        return $reflectionParameter->isOptional() ? $reflectionParameter->getDefaultValue() : null;
    }
}
