<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Event\Contract;

interface EventListenerInterface
{
    /**
     * @return iterable<mixed, class-string>
     */
    public function listen(): iterable;

    public function process(object $event): void;

    public function getPriority(): int;
}
