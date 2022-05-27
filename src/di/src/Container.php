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
use Max\Di\Container\DependencyFinder;
use Max\Di\Container\PropertyModifier;
use Max\Di\Container\ResolvingCallbacks;
use Max\Di\Exceptions\{ContainerException, NotFoundException};
use Max\Reflection\ReflectionManager;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface};
use ReflectionException;
use ReflectionFunction;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    use ResolvingCallbacks, PropertyModifier, DependencyFinder;

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
            $this->set($id, $this->resolve($id, $arguments));
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
     * @param string $id        标识
     * @param array  $arguments 构造函数参数列表
     *
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function resolve(string $id, array $arguments = []): object
    {
        $id              = $this->getBinding($id);
        $reflectionClass = ReflectionManager::reflectClass($id);
        if ($reflectionClass->isInterface()) {
            if (!$this->bound($id)) {
                throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
            }
            // TODO 当绑定的类并没有实现该接口
            $reflectionClass = ReflectionManager::reflectClass($this->getBinding($id));
        }

        return $this->resolving(
            $reflectionClass,
            $reflectionClass->newInstanceArgs($this->getConstructorArgs($reflectionClass, $arguments))
        );
    }

    /**
     * 调用类的方法
     *
     * @param array|Closure|string $callable  可调用的类或者实例和方法数组
     * @param array                $arguments 给方法传递的参数
     *
     * @throws ReflectionException|ContainerExceptionInterface|NotFoundException
     */
    public function call(array|Closure|string $callable, array $arguments = []): mixed
    {
        if ($callable instanceof Closure || is_string($callable)) {
            return $this->callFunc($callable, $arguments);
        }
        [$id, $method] = $callable;
        $id               = is_object($id) ? $id::class : $this->getBinding($id);
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
     * @param Closure|string $function  函数
     * @param array          $arguments 参数列表
     *
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function callFunc(Closure|string $function, array $arguments = []): mixed
    {
        $reflectFunction = new ReflectionFunction($function);

        return $reflectFunction->invokeArgs(
            $this->getFuncArgs($reflectFunction, $arguments)
        );
    }
}
