<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use ArrayObject;
use Closure;

class JoinPoint
{
    /**
     * @param string      $class      切入的类名
     * @param string      $method     切入的方法
     * @param ArrayObject $parameters 当前方法传递的参数列表【索引数组】
     */
    public function __construct(
        public string $class,
        public string $method,
        public ArrayObject $parameters,
        protected Closure $callback
    ) {
    }

    /**
     * 执行代理方法.
     */
    public function process(): mixed
    {
        return call_user_func_array($this->callback, $this->parameters->getArrayCopy());
    }
}
