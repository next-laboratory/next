<?php

namespace Max\Event\Tests\Events;

class FooEvent
{
    public function __construct(
        public string $value = '',
    )
    {
    }
}