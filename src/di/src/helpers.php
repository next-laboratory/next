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

use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

if (false === function_exists('container')) {
    /**
     * 容器实例化和获取实例
     */
    function container(): ContainerInterface
    {
        return Context::getContainer();
    }
}

if (false === function_exists('call')) {
    /**
     * 容器调用方法
     *
     * @param array|string|Closure $callback  数组、闭包、函数名
     * @param array                $arguments 给方法传递的参数列表
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    function call(array|string|Closure $callback, array $arguments = []): mixed
    {
        return container()->call($callback, $arguments);
    }
}

if (false === function_exists('make')) {
    /**
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    function make(string $id, array $parameters = [])
    {
        return container()->make($id, $parameters);
    }
}
