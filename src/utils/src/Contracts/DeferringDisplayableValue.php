<?php

namespace Max\Utils\Contracts;

/**
 * Most of the methods in this file come from illuminate
 * thanks Laravel Team provide such a useful class.
 */
interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return Htmlable|string
     */
    public function resolveDisplayableValue();
}
