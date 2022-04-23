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
use Max\Di\Context;
use Max\Utils\Pipeline;

/**
 * 方法代理Trait
 * 当一个类被代理，并且某个方法被切入，这个方法体就会被该方法包裹
 * 切面类必须实现\Max\Di\Contracts\PropertyAttribute接口
 * 支持多个切面注解类，执行逻辑类似中间件
 */
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
        return (new Pipeline(Context::getContainer()))
            ->send(new JoinPoint($this, $method, $parameters, $callback))
            ->through([...AspectCollector::getClassAspects(__CLASS__), ...AspectCollector::getMethodAspects(__CLASS__, $method)])
            ->via('process')
            ->then(function(JoinPoint $joinPoint) {
                return $joinPoint->process();
            });
    }
}
