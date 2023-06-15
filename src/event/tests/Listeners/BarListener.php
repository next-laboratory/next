<?php

namespace Max\Event\Tests\Listeners;

use Max\Event\EventListener;
use Max\Event\Tests\Events\BarEvent;
use Max\Event\Tests\Events\FooEvent;

class BarListener extends EventListener
{
    public function listen(): iterable
    {
        return [
            FooEvent::class,
            BarEvent::class,
        ];
    }

    public function process(object $event): void
    {
        switch (true) {
            case $event instanceof FooEvent:
                $event->value = self::class;
                break;
            case $event instanceof BarEvent:
                $event->value = 2;
        }
    }

    public function getPriority(): int
    {
        return 10;
    }
}