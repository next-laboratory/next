<?php

namespace Max\Event\Tests\Events;

use Psr\EventDispatcher\StoppableEventInterface;

class BarEvent implements StoppableEventInterface
{
    public int $value = 0;

    public function isPropagationStopped(): bool
    {
        return true;
    }
}