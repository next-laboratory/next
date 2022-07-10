<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Di;

use BadMethodCallException;
use Closure;
use Max\Di\Exceptions\ContainerException;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionUnionType;
use function is_null;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    /** @var array 类和标识对应关系 */
    protected array $bindings = [];

    /** @var array 已经解析实例 */
    protected array $resolved = [];

    /**
     * 将实例化的类存放到数组中.
     *
     * @param string $id       标识
     * @param object $instance 实例
     */
    public function set(string $id, object $instance)
    {
        $this->resolved[$this->getBinding($id)] = $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id)
    {
        return $this->resolved[$this->getBinding($id)] ?? new NotFoundException('No instance found: ' . $id);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return isset($this->resolved[$this->getBinding($id)]);
    }

    /**
     * @param string $id    标识，可以是接口
     * @param string $class 类名
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
     * 注入的外部接口方法.
     *
     * @template T
     *
     * @param class-string<T>|string $id        标识
     * @param array                  $arguments 构造函数参数列表（关联数组）
     *
     * @throws ContainerExceptionInterface|NotFoundException|ReflectionException
     * @return mixed|<T>
     */
    public function make(string $id, array $arguments = []): mixed
    {
        if ($this->has($id) === false) {
            $id              = $this->getBinding($id);
            $reflectionClass = Reflection::class($id);
            if ($reflectionClass->isInterface()) {
                if (! $this->bound($id)) {
                    throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
                }
                // TODO 当绑定的类并没有实现该接口
                $reflectionClass = Reflection::class($this->getBinding($id));
            }

            $this->set($id, $reflectionClass->newInstanceArgs($this->getConstructorArgs($reflectionClass, $arguments)));
        }
        return $this->get($id);
    }

    /**
     * 注销实例.
     */
    public function remove(string $id): void
    {
        $binding = $this->getBinding($id);
        if (isset($this->resolved[$binding])) {
            unset($this->resolved[$binding]);
        }
        if ($id !== $binding && isset($this->resolved[$id])) {
            unset($this->resolved[$id]);
        }
    }

    /**
     * 调用类的方法.
     *
     * @param array|Closure|string $callable  $callable 可调用的类或者实例和方法数组
     * @param array                $arguments 给方法传递的参数（关联数组）
     *
     * @throws ContainerExceptionInterface|ReflectionException
     */
    public function call(array|string|Closure $callable, array $arguments = []): mixed
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return $this->callFunc($callable, $arguments);
        }
        [$objectOrClass, $method] = $callable;
        $isObject                 = is_object($objectOrClass);
        $reflectionMethod         = Reflection::method($isObject ? get_class($objectOrClass) : $this->getBinding($objectOrClass), $method);
        if ($reflectionMethod->isAbstract() === false) {
            if (! $reflectionMethod->isPublic()) {
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
     * 调用闭包.
     *
     * @param Closure|string $function  函数
     * @param array          $arguments 参数列表（关联数组）
     *
     * @throws NotFoundException|ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function callFunc(string|Closure $function, array $arguments = [])
    {
        $reflectFunction = new ReflectionFunction($function);

        return $reflectFunction->invokeArgs(
            $this->getFuncArgs($reflectFunction, $arguments)
        );
    }

    /**
     * 获取构造函数的参数.
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
     * @param array                      $arguments          参数列表（关联数组）
     *
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    public function getFuncArgs(ReflectionFunctionAbstract $reflectionFunction, array $arguments = []): array
    {
        $funcArgs = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $arguments)) {
                $injectValue = $arguments[$name];
                $type        = $parameter->getType();
                if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
                    $injectValue = $this->castParameter($injectValue, $type->getName());
                }
                $funcArgs[] = $injectValue;
            } else {
                $type = $parameter->getType();
                if (is_null($type)
                    || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                    || $type instanceof ReflectionUnionType
                    || ($typeName = $type->getName()) === 'Closure'
                ) {
                    $funcArgs[] = $parameter->isOptional()
                        ? $parameter->getDefaultValue()
                        : throw new ContainerException(sprintf('Missing parameter `%s`', $name));
                } else {
                    try {
                        $funcArgs[] = $this->make($typeName);
                    } catch (ReflectionException|ContainerExceptionInterface $exception) {
                        $funcArgs[] = $parameter->isOptional() ? $parameter->getDefaultValue() : throw $exception;
                    }
                }
            }
        }

        return $funcArgs;
    }

    /**
     * 转换类型.
     * @param mixed $value
     */
    protected function castParameter($value, string $type): mixed
    {
        return match ($type) {
            'int'    => (int) $value,
            'string' => (string) $value,
            'bool'   => (bool) $value,
            'array'  => (array) $value,
            'float', 'double' => (float) $value,
            'object' => (object) $value,
            default  => $value,
        };
    }
}
