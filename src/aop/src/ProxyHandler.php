<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use ArrayObject;
use Closure;
use Next\Aop\Collector\AspectCollector;
use Next\Di\Reflection;
use ReflectionException;

trait ProxyHandler
{
    /**
     * @throws ReflectionException
     */
    protected static function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        $class = static::class;
//        /** @var AspectInterface $aspect */
//        $pipeline         = array_reduce(
//            array_reverse(AspectCollector::getMethodAspects($class, $method)),
//            fn($stack, $aspect) => fn(JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
//            fn(JoinPoint $joinPoint) => $joinPoint->process()
//        );
        $args             = new ArrayObject();
        $methodParameters = Reflection::methodParameterNames($class, $method);
        foreach ($parameters as $key => $parameter) {
            $args->offsetSet($methodParameters[$key], $parameter);
        }

        $aspects = AspectCollector::getMethodAspects($class, $method);
        return (new ProceedingJoinPoint($aspects, $callback, $class, $method, $args))->process();
//        return $pipeline(new JoinPoint($class, $method, $funcArgs, $callback));
    }
}
