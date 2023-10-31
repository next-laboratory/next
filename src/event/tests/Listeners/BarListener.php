<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Tests\Listeners;

use Next\Event\EventListener;
use Next\Event\Tests\Events\BarEvent;
use Next\Event\Tests\Events\FooEvent;

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
