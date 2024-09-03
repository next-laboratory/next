<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils;

use Next\Utils\Contract\ValidatedData;

class ValidatedInput implements ValidatedData
{
    /**
     * The underlying input.
     *
     * @var array
     */
    protected $input;

    /**
     * Create a new validated input container.
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Dynamically access input data.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->input[$name];
    }

    /**
     * Dynamically set input data.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        $this->input[$name] = $value;
    }

    /**
     * Determine if an input key is set.
     *
     * @param  mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->input[$name]);
    }

    /**
     * Remove an input key.
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->input[$name]);
    }

    /**
     * Get a subset containing the provided keys with values from the input data.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function only($keys)
    {
        $results = [];

        $input = $this->input;

        $placeholder = new \stdClass();

        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = data_get($input, $key, $placeholder);

            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param array|mixed $keys
     *
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->input;

        Arr::forget($results, $keys);

        return $results;
    }

    /**
     * Merge the validated input with the given array of additional data.
     *
     * @return static
     */
    public function merge(array $items)
    {
        return new static(array_merge($this->input, $items));
    }

    /**
     * Get the input as a collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collect()
    {
        return new Collection($this->input);
    }

    /**
     * Get the raw, underlying input array.
     *
     * @return array
     */
    public function all()
    {
        return $this->input;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     */
    public function offsetExists($key): bool
    {
        return isset($this->input[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     */
    public function offsetGet($key): mixed
    {
        return $this->input[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->input[] = $value;
        } else {
            $this->input[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     */
    public function offsetUnset($key): void
    {
        unset($this->input[$key]);
    }

    /**
     * Get an iterator for the input.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->input);
    }
}
