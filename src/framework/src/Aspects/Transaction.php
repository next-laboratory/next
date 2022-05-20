<?php

namespace Max\Framework\Aspects;

use Closure;
use Max\Database\Manager;
use Max\Aop\JoinPoint;
use Max\Aop\Contracts\AspectInterface;
use Max\Pool\PoolManager;
use ReflectionException;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transaction implements AspectInterface
{
    /**
     * 连接
     *
     * @param string|null $connection
     */
    public function __construct(protected ?string $connection = null)
    {
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        return PoolManager::get($this->connection)->get()->transaction(function() use ($joinPoint, $next) {
            return $next($joinPoint);
        });
    }
}
