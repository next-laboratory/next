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

namespace Max\Container;

use BadMethodCallException;
use Closure;
use Exception;
use Max\Container\Exceptions\{ContainerException, NotFoundException};
use Psr\Container\{ContainerExceptionInterface, ContainerInterface};
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use function array_shift;
use function is_null;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected array $bindings = [];

    /**
     * @var array
     */
    protected array $resolved = [];

    /**
     * 容器嗅探方法
     */
    protected const VIA = '__new';

    /**
     * @var array
     */
    protected array $globalResolvingCallbacks = [];

    /**
     * @var array
     */
    protected array $resolvingCallbacks = [];

    /**
     * 将实例化的类存放到数组中
     *
     * @param string $id 标识
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
    public function has(string $id)
    {
        return isset($this->resolved[$this->getBinding($id)]);
    }

    /**
     * @param string $id
     * @param string $class
     *
     * @return void
     */
    public function bind(string $id, string $class): void
    {
        $this->bindings[$id] = $class;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function unBind(string $id): void
    {
        if ($this->bound($id)) {
            unset($this->bindings[$id]);
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function bound(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function getBinding(string $id): string
    {
        return $this->bindings[$id] ?? $id;
    }

    /**
     * 注入的外部接口方法
     *
     * @param string $id 类标识
     * @param array $arguments 参数列表
     *
     * @return mixed
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function make(string $id, array $arguments = []): object
    {
        if (false === $this->has($id)) {
            $this->set($id, $this->resolve($id, $arguments));
        }
        return $this->get($id);
    }

    /**
     * 注销实例
     *
     * @param string $id
     *
     * @return void
     */
    public function remove(string $id): void
    {
        $id = $this->getBinding($id);
        if ($this->has($id)) {
            unset($this->resolved[$id]);
        }
    }

    /**
     * @param string $id
     * @param array $arguments
     *
     * @return object
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function resolve(string $id, array $arguments = []): object
    {
        $id = $this->getBinding($id);
        $reflectionClass = ReflectionManager::reflectClass($id);
        if ($reflectionClass->isInterface()) {
            if (!$this->bound($id)) {
                throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
            }
            // TODO 当绑定的类并没有实现该接口
            $reflectionClass = ReflectionManager::reflectClass($this->getBinding($id));
        }

        if ($reflectionClass->hasMethod(static::VIA)) {
            $maker = $reflectionClass->getMethod(static::VIA);
            if ($maker->isPublic() && $maker->isStatic()) {
                return $this->resolving(
                    $reflectionClass,
                    $maker->invokeArgs(null, $this->getFuncArgs($maker, $arguments))
                );
            }
        }

        return $this->resolving(
            $reflectionClass,
            $reflectionClass->newInstanceArgs($this->getConstructorArgs($reflectionClass, $arguments))
        );
    }

    /**
     * 调用类的方法
     *
     * @param array|Closure|string $callable 可调用的类或者实例和方法数组
     * @param array $arguments 给方法传递的参数
     *
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function call(array|Closure|string $callable, array $arguments = []): mixed
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return $this->callFunc($callable, $arguments);
        }
        [$id, $method] = $callable;
        $id = is_object($id) ? $id::class : $this->getBinding($id);
        $reflectionMethod = ReflectionManager::reflectMethod($id, $method);
        if (false === $reflectionMethod->isAbstract()) {
            $funcArgs = $this->getFuncArgs($reflectionMethod, $arguments);
            if (!$reflectionMethod->isPublic()) {
                $reflectionMethod->setAccessible(true);
            }
            if ($reflectionMethod->isStatic()) {
                return $reflectionMethod->invokeArgs(null, $funcArgs);
            }
            return $reflectionMethod->invokeArgs(
                is_object($id) ? $id : $this->make($id),
                $funcArgs
            );
        }
        throw new BadMethodCallException('Unable to call method: ' . $method);
    }

    /**
     * 调用闭包
     *
     * @param Closure|string $function
     * @param array $arguments
     *
     * @return mixed
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function callFunc(Closure|string $function, array $arguments = []): mixed
    {
        $reflectFunction = ReflectionManager::reflectFunction($function);

        return $reflectFunction->invokeArgs(
            $this->getFuncArgs($reflectFunction, $arguments)
        );
    }

    /**
     * 获取构造函数的参数
     *
     * @param ReflectionClass $reflectionClass
     * @param array $arguments
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
     * @param array $arguments 参数列表，支持关联数组，会自动按照变量名传入
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

    /**
     * 设置属性[未测试]
     *
     * @param        $object
     * @param string $property
     * @param null $value
     *
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function setProperty($object, string $property, $value = null): void
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
     * @throws ContainerExceptionInterface
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
     * @param object $object
     * @param ReflectionProperty $reflectionProperty
     * @param                     $value
     */
    public function setValue(object $object, ReflectionProperty $reflectionProperty, $value): void
    {
        $this->setAccessible($reflectionProperty)->setValue($object, $value);
    }

    /**
     * 解析后回调
     *
     * @param ReflectionClass $reflectionClass
     * @param object $concrete
     *
     * @return object
     */
    protected function resolving(ReflectionClass $reflectionClass, object $concrete): object
    {
        foreach ($this->getResolvingCallbacks($concrete) as $callback) {
            $callback($this, $reflectionClass, $concrete);
        }

        return $concrete;
    }

    /**
     * @param $abstract
     *
     * @return array
     */
    public function getResolvingCallbacks($abstract): array
    {
        $abstract = is_object($abstract) ? $abstract::class : $abstract;

        return $this->globalResolvingCallbacks + ($this->resolvingCallbacks[$abstract] ?? []);
    }

    /**
     * 解析后回调
     *
     * @param               $abstract
     * @param Closure|null $callback
     */
    public function afterResolving($abstract, ?Closure $callback = null): void
    {
        if ($abstract instanceof Closure && is_null($callback)) {
            $this->globalResolvingCallbacks[] = $abstract;
        } else {
            $this->resolvingCallbacks[$abstract][] = $callback;
        }
    }
}
