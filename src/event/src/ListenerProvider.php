<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event;

use Next\Event\Contract\EventListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, EventListenerInterface[]>
     */
    protected array $events = [];

    /**
     * 已经注册的监听器.
     *
     * @var array<class-string, EventListenerInterface[]>
     */
    protected array $listeners = [];

    /**
     * 注册单个事件监听.
     */
    public function addListener(EventListenerInterface ...$eventListeners): void
    {
        if (empty($eventListeners)) {
            return;
        }
        foreach ($eventListeners as $eventListener) {
            $listenerClass = $eventListener::class;
            if (! isset($this->listeners[$listenerClass])) {
                $this->listeners[$listenerClass] = $eventListener;
                foreach ($eventListener->listen() as $event) {
                    $this->events[$event][] = $eventListener;
                }
            }
        }
    }

    /**
     * @return iterable<int, EventListenerInterface>
     */
    public function getListenersForEvent(object $event): iterable
    {
        $listeners        = $this->events[$event::class] ?? [];
        $splPriorityQueue = new \SplPriorityQueue();
        foreach ($listeners as $listener) {
            $splPriorityQueue->insert($listener, $listener->getPriority());
        }

        return $splPriorityQueue;
    }
}
