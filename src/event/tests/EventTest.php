<?php

namespace Max\Event\Tests;

use Max\Event\EventDispatcher;
use Max\Event\ListenerProvider;
use Max\Event\Tests\Events\BarEvent;
use Max\Event\Tests\Events\FooEvent;
use Max\Event\Tests\Listeners\BarListener;
use Max\Event\Tests\Listeners\FooListener;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    protected EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addListener(new FooListener(), new BarListener());
        $this->eventDispatcher = new EventDispatcher($listenerProvider);
    }

    public function testEvent()
    {
        $fooEvent = $this->eventDispatcher->dispatch(new FooEvent());

        $this->assertEquals(FooListener::class, $fooEvent->value);
    }

    public function testStoppableEvent()
    {
        $barEvent = $this->eventDispatcher->dispatch(new BarEvent());

        $this->assertEquals(2, $barEvent->value);
    }
}