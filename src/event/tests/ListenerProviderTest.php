<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Tests;

use Next\Event\ListenerProvider;
use Next\Event\Tests\Events\FooEvent;
use Next\Event\Tests\Listeners\BarListener;
use Next\Event\Tests\Listeners\FooListener;
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
