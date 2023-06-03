<?php

namespace Max\Event\Tests;

use Max\Event\EventDispatcher;
use Max\Event\ListenerProvider;
use Max\Event\Tests\Events\FooEvent;
use Max\Event\Tests\Listeners\BarListener;
use Max\Event\Tests\Listeners\FooListener;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEvent()
    {
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addListener(new FooListener(), new BarListener());
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $fooEvent = $eventDispatcher->dispatch(new FooEvent());

        $this->assertEquals(FooListener::class, $fooEvent->value);
    }
}