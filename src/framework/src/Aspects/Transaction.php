<?php

namespace Max\Framework\Aspects;

use Closure;
use Max\Database\Manager;
use Max\Di\Aop\JoinPoint;
use Max\Di\Contracts\AspectInterface;
use ReflectionException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transaction implements AspectInterface
{
    /**
     * @param JoinPoint $joinPoint
     * @param Closure $next
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        return make(Manager::class)->transaction(function () use ($joinPoint, $next) {
            return $next($joinPoint);
        });
    }
}
