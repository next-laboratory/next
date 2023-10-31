<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
trait Macroable
{
    /**
     * The registered string macros.
     */
    protected static array $macros = [];

    /**
     * Dynamically handle calls to the class.
     *
     * @throws BadMethodCallException
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @throws BadMethodCallException
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Register a custom macro.
     */
    public static function macro(string $name, callable|object $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Mix another object into the class.
     *
     * @throws ReflectionException
     */
    public static function mixin(object $mixin, bool $replace = true): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || ! static::hasMacro($method->name)) {
                $method->setAccessible(true);
                static::macro($method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * Checks if macro is registered.
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }
}
