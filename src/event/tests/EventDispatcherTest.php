<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event\Tests;

use Max\Event\EventDispatcher;
use Max\Event\ListenerProvider;
use Max\Event\Tests\Events\BarEvent;
use Max\Event\Tests\Events\FooEvent;
use Max\Event\Tests\Listeners\BarListener;
use Max\Event\Tests\Listeners\FooListener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class EventDispatcherTest extends TestCase
{
    protected EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        $listenerProvider = new ListenerProvider();
        $listenerProvider->addListener(new FooListener(), new BarListener());
        $this->eventDispatcher = new EventDispatcher($listenerProvider);
    }

    public function testDispatch()
    {
        $fooEvent = $this->eventDispatcher->dispatch(new FooEvent());

        $this->assertEquals(FooListener::class, $fooEvent->value);
    }

    public function testStoppableEventDispatch()
    {
        $barEvent = $this->eventDispatcher->dispatch(new BarEvent());

        $this->assertEquals(2, $barEvent->value);
    }
}
