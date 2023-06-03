<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event\Contract;

interface EventListenerInterface
{
    /**
     * @return iterable<mixed, class-string>
     */
    public function listen(): iterable;

    public function process(object $event): void;

    public function getPriority(): int;
}
