<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class Optional implements \ArrayAccess
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * Create a new optional instance.
     *
     * @param mixed $value the underlying object
     */
    public function __construct(
        protected mixed $value
    ) {}

    /**
     * Dynamically access a property on the underlying object.
     */
    public function __get(string $key)
    {
        if (is_object($this->value)) {
            return $this->value->{$key} ?? null;
        }
        return null;
    }

    /**
     * Dynamically check a property exists on the underlying object.
     *
     * @return bool
     */
    public function __isset(mixed $name)
    {
        if (is_object($this->value)) {
            return isset($this->value->{$name});
        }

        if (is_array($this->value) || $this->value instanceof \ArrayObject) {
            return isset($this->value[$name]);
        }

        return false;
    }

    /**
     * Dynamically pass a method to the underlying object.
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (is_object($this->value)) {
            return $this->value->{$method}(...$parameters);
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return Arr::accessible($this->value) && Arr::exists($this->value, $key);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return Arr::get($this->value, $key);
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        if (Arr::accessible($this->value)) {
            $this->value[$key] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        if (Arr::accessible($this->value)) {
            unset($this->value[$key]);
        }
    }
}
