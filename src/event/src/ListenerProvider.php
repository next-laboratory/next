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

use Max\Event\Contracts\EventListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array
     */
    protected array $events = [];

    /**
     * 已经注册的监听器
     *
     * @var array
     */
    protected array $listeners = [];

    /**
     * 注册单个事件监听
     *
     * @param EventListenerInterface $eventListener
     */
    public function addListener(EventListenerInterface $eventListener)
    {
        $listener = $eventListener::class;
        if (!$this->listened($listener)) {
            $this->listeners[$listener] = $eventListener;
            foreach ($eventListener->listen() as $event) {
                $this->events[$event][] = $eventListener;
            }
        }
    }

    /**
     * @return array
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * 判断是否已经监听
     *
     * @param string $listeners
     *
     * @return bool
     */
    public function listened(string $listeners): bool
    {
        return isset($this->listeners[$listeners]);
    }

    /**
     * 获取监听器
     *
     * @param object $event
     *
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->events[$event::class] ?? [];
    }
}
