<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Database\Event;

class QueryExecuted
{
    public function __construct(
        public string $query,
        public array $bindings,
        public float $time,
    ) {
    }
}
