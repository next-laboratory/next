<?php

namespace Max\Event;

use Max\Event\Contract\EventListenerInterface;

abstract class EventListener implements EventListenerInterface
{
    public function getPriority(): int
    {
        return 0;
    }
}