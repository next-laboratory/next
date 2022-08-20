<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Proxy;

use Max\Utils\Contract\Enumerable;

/**
 * @mixin Enumerable
 */
class HigherOrderCollectionProxy
{
    /**
     * The collection being operated on.
     *
     * @var Enumerable
     */
    protected $collection;

    /**
     * The method being proxied.
     *
     * @var string
     */
    protected $method;

    /**
     * Create a new proxy instance.
     *
     * @param Enumerable $collection
     * @param string     $method
     *
     * @return void
     */
    public function __construct(Enumerable $collection, $method)
    {
        $this->method     = $method;
        $this->collection = $collection;
    }

    /**
     * Proxy accessing an attribute onto the collection items.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->collection->{$this->method}(function($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Proxy a method call onto the collection items.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->collection->{$this->method}(function($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}
