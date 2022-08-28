<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Event;

class QueryExecuted
{
    public function __construct(
        public string $query,
        public array $bindings,
        public float $executedAt
    ) {
    }
}
