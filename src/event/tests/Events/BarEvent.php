<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Tests\Events;

use Psr\EventDispatcher\StoppableEventInterface;

class BarEvent implements StoppableEventInterface
{
    public int $value = 0;

    public function isPropagationStopped(): bool
    {
        return true;
    }
}
