<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Proxy;

/*
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */

use Max\Utils\Contract\Enumerable;

/**
 * @mixin Enumerable
 * Most of the methods in this file come from illuminate/support,
 * thanks Laravel Team provide such a useful class.
 */
class HigherOrderWhenProxy
{
    /**
     * Create a new proxy instance.
     *
     * @param Enumerable $collection the collection being operated on
     * @param bool       $condition  the condition for proxying
     */
    public function __construct(
        protected Enumerable $collection,
        protected bool $condition
    ) {
    }

    /**
     * Proxy accessing an attribute onto the collection.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->condition
            ? $this->collection->{$key}
            : $this->collection;
    }

    /**
     * Proxy a method call onto the collection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->condition
            ? $this->collection->{$method}(...$parameters)
            : $this->collection;
    }
}
