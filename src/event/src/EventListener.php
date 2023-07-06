<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

use Max\Event\Contract\EventListenerInterface;

abstract class EventListener implements EventListenerInterface
{
    public function getPriority(): int
    {
        return 0;
    }
}
