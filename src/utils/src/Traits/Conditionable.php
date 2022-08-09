<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Traits;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
trait Conditionable
{
    /**
     * Apply the callback if the given "value" is truthy.
     */
    public function when(mixed $value, callable $callback, ?callable $default = null): mixed
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }
        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }

    /**
     * Apply the callback if the given "value" is falsy.
     */
    public function unless(mixed $value, callable $callback, ?callable $default = null): mixed
    {
        if (! $value) {
            return $callback($this, $value) ?: $this;
        }
        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }
}
