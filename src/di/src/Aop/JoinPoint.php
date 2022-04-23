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

namespace Max\Di\Aop;

use Closure;
use Max\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class JoinPoint
{
    /**
     * @param object  $object     切入类的当前实例
     * @param string  $method     切入的方法
     * @param array   $parameters 当前方法传递的参数列表【索引数组】
     * @param Closure $callback
     */
    public function __construct(
        public object     $object,
        public string     $method,
        public array      $parameters,
        protected Closure $callback
    )
    {
    }

    /**
     * 执行代理方法
     *
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function process(): mixed
    {
        return call($this->callback, $this->parameters);
    }
}
