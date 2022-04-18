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
use Max\Di\Container\{DependencyFinder, PropertyModifier, ResolvingCallbacks};
use Max\Di\Exceptions\{ContainerException, NotFoundException};
use Psr\Container\{ContainerExceptionInterface, ContainerInterface};
use ReflectionException;
use function is_object;
use function is_string;

class Container implements ContainerInterface
{
    use ResolvingCallbacks, PropertyModifier, DependencyFinder;

    /**
     * 别名
     *
     * @var array
     */
    protected array $alias = [];

    /**
     * @var array
     */
    protected array $resolved = [];

    /**
     * 容器嗅探方法
     */
    protected const VIA = '__new';

    /**
     * 将实例化的类存放到数组中
     *
     * @param string $id       标识
     * @param object $instance 实例
     */
    public function set(string $id, object $instance)
    {
        $this->resolved[$this->getAlias($id)] = $instance;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if ($this->has($id)) {
            return $this->resolved[$this->getAlias($id)];
        }
        throw new NotFoundException('No instance found: ' . $id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->resolved[$this->getAlias($id)]);
    }

    /**
     * 添加绑定
     *
     * @param string $id
     * @param string $class
     *
     * @return void
     */
    public function alias(string $id, string $class): void
    {
        $this->alias[$id] = $class;
    }

    /**
     * 移除别名
     *
     * @param string $id
     *
     * @return void
     */
    public function unAlias(string $id): void
    {
        if ($this->hasAlias($id)) {
            unset($this->alias[$id]);
        }
    }

    /**
     * 判断是否有别名
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasAlias(string $id): bool
    {
        return isset($this->alias[$id]);
    }

    /**
     * 通过标识获取别名
     *
     * @param string $id
     *
     * @return string
     */
    public function getAlias(string $id): string
    {
        return $this->alias[$id] ?? $id;
    }

    /**
     * 注入的外部接口方法
     *
     * @param string $id        类标识
     * @param array  $arguments 参数列表
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
        $id = $this->getAlias($id);
        if ($this->has($id)) {
            unset($this->resolved[$id]);
        }
    }

    /**
     * @param string $id
     * @param array  $arguments
     *
     * @return object
     * @throws ReflectionException|NotFoundException|ContainerExceptionInterface
     */
    public function resolve(string $id, array $arguments = []): object
    {
        $id              = $this->getAlias($id);
        $reflectionClass = ReflectionManager::reflectClass($id);
        if ($reflectionClass->isInterface()) {
            if (!$this->hasAlias($id)) {
                throw new ContainerException('The ' . $id . ' has no implementation class. ', 600);
            }
            // TODO 当绑定的类并没有实现该接口
            $reflectionClass = ReflectionManager::reflectClass($this->getAlias($id));
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
     * @param array|Closure|string $callable  可调用的类或者实例和方法数组
     * @param array                $arguments 给方法传递的参数
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
        $id               = is_object($id) ? $id::class : $this->getAlias($id);
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
     * @param array          $arguments
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
}
