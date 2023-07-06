<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

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
