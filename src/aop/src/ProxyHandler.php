<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop;

use ArrayObject;
use Closure;
use Max\Aop\Collectors\AspectCollector;
use Max\Aop\Contracts\AspectInterface;
use Max\Di\Reflection;
use ReflectionException;

trait ProxyHandler
{
    protected static array $__aspectCache = [];

    /**
     * @throws ReflectionException
     */
    protected function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        if (! isset(static::$__aspectCache[$method])) {
            static::$__aspectCache[$method] = array_reverse(AspectCollector::getMethodAspects(__CLASS__, $method));
        }
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            self::$__aspectCache[$method],
            fn ($stack, $aspect) => fn (JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn (JoinPoint $joinPoint) => $joinPoint->process()
        );
        return $pipeline(
            new JoinPoint($this, $method, new ArrayObject(
                array_combine(Reflection::methodParameterNames(__CLASS__, $method), $parameters)
            ), $callback)
        );
    }
}
