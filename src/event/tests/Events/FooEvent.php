<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Tests\Events;

class FooEvent
{
    public function __construct(
        public string $value = '',
    ) {
    }
}
