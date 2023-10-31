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
use Next\Aop\Contract\AspectInterface;
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
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            array_reverse(AspectCollector::getMethodAspects($class, $method)),
            fn ($stack, $aspect) => fn (JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn (JoinPoint $joinPoint) => $joinPoint->process()
        );
        $funcArgs         = new ArrayObject();
        $methodParameters = Reflection::methodParameterNames($class, $method);
        foreach ($parameters as $key => $parameter) {
            $funcArgs->offsetSet($methodParameters[$key], $parameter);
        }
        return $pipeline(new JoinPoint($class, $method, $funcArgs, $callback));
    }
}
