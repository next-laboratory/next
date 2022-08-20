<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Proxy;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
class HigherOrderTapProxy
{
    /**
     * Create a new tap proxy instance.
     *
     * @param mixed $target the target being tapped
     */
    public function __construct(
        protected mixed $target
    ) {
    }

    /**
     * Dynamically pass method calls to the target.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->target->{$method}(...$parameters);

        return $this->target;
    }
}
