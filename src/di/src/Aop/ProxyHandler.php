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

trait ProxyHandler
{
    /**
     * @param string  $function
     * @param Closure $callback
     * @param array   $arguments
     *
     * @return mixed
     */
    protected function __callViaProxy(string $function, Closure $callback, array $arguments): mixed
    {
        return (new Pipeline(Context::getContainer()))
            ->send(new JoinPoint($this, $function, $arguments, $callback))
            ->through([...AspectCollector::getClassAspects(__CLASS__), ...AspectCollector::getMethodAspects(__CLASS__, $function)])
            ->via('process')
            ->then(function(JoinPoint $joinPoint) {
                return $joinPoint->process();
            });
    }
}
