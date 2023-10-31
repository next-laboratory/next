<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

use Next\Di\Context;
use Next\Di\Exception\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

if (function_exists('container') === false) {
    /**
     * 容器实例化和获取实例.
     */
    function container(): ContainerInterface
    {
        return Context::getContainer();
    }
}

if (function_exists('call') === false) {
    /**
     * 容器调用方法.
     *
     * @param array|Closure|string $callback  数组、闭包、函数名
     * @param array                $arguments 给方法传递的参数列表
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    function call(array|string|Closure $callback, array $arguments = []): mixed
    {
        return container()->call($callback, $arguments);
    }
}

if (function_exists('make') === false) {
    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @throws NotFoundException
     * @throws ContainerExceptionInterface|ReflectionException
     * @return T
     */
    function make(string $id, array $parameters = [])
    {
        return container()->make($id, $parameters);
    }
}
