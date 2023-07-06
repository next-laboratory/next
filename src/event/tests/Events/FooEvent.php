<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event\Tests\Events;

class FooEvent
{
    public function __construct(
        public string $value = '',
    ) {
    }
}
