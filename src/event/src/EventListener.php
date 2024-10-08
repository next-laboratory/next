<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event;

use Next\Event\Contract\EventListenerInterface;

abstract class EventListener implements EventListenerInterface
{
    public function getPriority(): int
    {
        return 0;
    }
}
