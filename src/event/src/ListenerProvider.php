<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

use Max\Event\Contract\EventListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * 事件.
     */
    protected array $events = [];

    /**
     * 已经注册的监听器.
     *
     * @var EventListenerInterface[]
     */
    protected array $listeners = [];

    /**
     * 注册单个事件监听.
     */
    public function addListener(EventListenerInterface $eventListener)
    {
        $listener = $eventListener::class;
        if (! $this->listened($listener)) {
            $this->listeners[$listener] = $eventListener;
            foreach ($eventListener->listen() as $event) {
                $this->events[$event][] = $eventListener;
            }
        }
    }

    /**
     * 获取所有监听器.
     *
     * @return EventListenerInterface[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * 判断是否已经监听.
     */
    public function listened(string $listeners): bool
    {
        return isset($this->listeners[$listeners]);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->events[$event::class] ?? [];
    }
}
