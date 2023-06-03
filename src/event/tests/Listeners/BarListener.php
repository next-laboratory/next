<?php

namespace Max\Event\Tests\Listeners;

use Max\Event\EventListener;
use Max\Event\Tests\Events\FooEvent;

class BarListener extends EventListener
{
    public function listen(): iterable
    {
        return [
            FooEvent::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof FooEvent) {
            var_dump('------------BAR--------------');
            $event->value = self::class;
        }
    }

    public function getPriority(): int
    {
        return 10;
    }
}