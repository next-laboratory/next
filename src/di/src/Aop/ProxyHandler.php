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
use Max\Di\Annotation\Collector\AspectCollector;
use Max\Di\Contracts\AspectInterface;

trait ProxyHandler
{
    /**
     * @param string  $method     方法名
     * @param Closure $callback   构造的函数
     * @param array   $parameters 方法参数
     *
     * @return mixed
     */
    protected function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            array_reverse([...AspectCollector::getClassAspects(__CLASS__), ...AspectCollector::getMethodAspects(__CLASS__, $method)]),
            fn($stack, $aspect) => fn(JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn(JoinPoint $joinPoint) => $joinPoint->process()
        );
        return $pipeline(new JoinPoint($this, $method, $parameters, $callback));
    }
}
