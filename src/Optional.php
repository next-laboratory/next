<?php

namespace Max\Utils;

use ArrayAccess;
use ArrayObject;
use Max\Macro\Macroable;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class Optional implements ArrayAccess
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The underlying object.
     */
    protected mixed $value;

    /**
     * Create a new optional instance.
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

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

        if (is_array($this->value) || $this->value instanceof ArrayObject) {
            return isset($this->value[$name]);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return Arr::accessible($this->value) && Arr::exists($this->value, $key);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return Arr::get($this->value, $key);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        if (Arr::accessible($this->value)) {
            $this->value[$key] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        if (Arr::accessible($this->value)) {
            unset($this->value[$key]);
        }
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
}
