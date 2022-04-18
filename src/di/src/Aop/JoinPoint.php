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
     * @param object   $proxy
     * @param string   $function
     * @param array    $arguments
     * @param Closure $callback
     */
    public function __construct(
        protected object   $proxy,
        protected string   $function,
        protected array    $arguments,
        protected Closure $callback
    )
    {
    }

    /**
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function process()
    {
        return call($this->callback, $this->arguments);
    }

    /**
     * @return object
     */
    public function getProxy(): object
    {
        return $this->proxy;
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
