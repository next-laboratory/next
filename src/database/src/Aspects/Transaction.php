<?php

namespace Max\Database\Aspects;

use Closure;
use Max\Context\Context;
use Max\Database\Context\Connection;
use Max\Di\Aop\JoinPoint;
use Max\Di\Contracts\AspectInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Transaction implements AspectInterface
{
    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        /**
         * bug
         */
        /** @var \PDO $PDO */
        $PDO = Context::get(Connection::class)['item'];
        $PDO->beginTransaction();
        try {
            $result = $next($joinPoint);
            $PDO->commit();
            return $result;
        } catch (\Throwable $throwable) {
            $PDO->rollBack();
            throw $throwable;
        }
    }
}
