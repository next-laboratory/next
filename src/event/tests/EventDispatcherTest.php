<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Tests;

use Next\Event\EventDispatcher;
use Next\Event\ListenerProvider;
use Next\Event\Tests\Events\BarEvent;
use Next\Event\Tests\Events\FooEvent;
use Next\Event\Tests\Listeners\BarListener;
use Next\Event\Tests\Listeners\FooListener;
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
