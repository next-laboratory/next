<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Max\Event\Contracts\EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        protected ListenerProvider $listenerProvider
    )
    {
    }

    /**
     * @inheritdoc
     */
    public function dispatch(object $event)
    {
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener->process($event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    public function getListenerProvider(): ListenerProvider
    {
        return $this->listenerProvider;
    }
}
