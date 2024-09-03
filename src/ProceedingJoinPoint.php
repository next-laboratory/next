<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use Next\Aop\Contract\AspectInterface;

class ProceedingJoinPoint
{
    /**
     * @param string                      $class      切入的类名
     * @param string                      $method     切入的方法
     * @param \ArrayObject                $parameters 当前方法传递的参数列表【索引数组】
     * @param array<int, AspectInterface> $aspects
     */
    public function __construct(
        protected array $aspects,
        protected \Closure $callback,
        public string $class,
        public string $method,
        public \ArrayObject $parameters,
    ) {}

    public function process()
    {
        if ($aspect = array_shift($this->aspects)) {
            return $aspect->process($this);
        }

        return $this->proceed();
    }

    /**
     * 执行代理方法.
     */
    public function proceed(): mixed
    {
        return call_user_func_array($this->callback, $this->parameters->getArrayCopy());
    }
}
