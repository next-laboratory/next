<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event\Tests;

use Max\Event\ListenerProvider;
use Max\Event\Tests\Events\FooEvent;
use Max\Event\Tests\Listeners\BarListener;
use Max\Event\Tests\Listeners\FooListener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ListenerProviderTest extends TestCase
{
    protected ListenerProvider $listenerProvider;

    protected function setUp(): void
    {
        $this->listenerProvider = new ListenerProvider();
    }

    public function testAddListeners()
    {
        $this->listenerProvider->addListener(new FooListener(), new BarListener());
        $listeners       = $this->listenerProvider->getListenersForEvent(new FooEvent());
        $listenerClasses = [];
        foreach ($listeners as $listener) {
            $listenerClasses[] = $listener::class;
        }
        $this->assertEquals([BarListener::class, FooListener::class], $listenerClasses);
    }
}
