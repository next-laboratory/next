<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use ArrayAccess;
use Next\Utils\Contract\Arrayable;
use Next\Utils\Contract\Jsonable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements Arrayable<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
class Fluent implements Arrayable, \ArrayAccess, Jsonable, \JsonSerializable
{
    /**
     * All of the attributes set on the fluent instance.
     *
     * @var array<TKey, TValue>
     */
    protected $attributes = [];

    /**
     * Create a new fluent instance.
     *
     * @param iterable<TKey, TValue> $attributes
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Handle dynamic calls to the fluent instance to set attributes.
     *
     * @param TKey              $method
     * @param array{0: ?TValue} $parameters
     *
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param TKey $key
     *
     * @return null|TValue
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param TKey   $key
     * @param TValue $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param TKey $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param TKey $key
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get an attribute from the fluent instance.
     *
     * @template TGetDefault
     *
     * @param TKey                                  $key
     * @param (\Closure(): TGetDefault)|TGetDefault $default
     *
     * @return TGetDefault|TValue
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return value($default);
    }

    /**
     * Get the attributes from the fluent instance.
     *
     * @return array<TKey, TValue>
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convert the fluent instance to an array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the fluent instance to JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param TKey $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param TKey $offset
     *
     * @return null|TValue
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param TKey   $offset
     * @param TValue $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param TKey $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }
}
