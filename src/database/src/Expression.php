<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

use Stringable;

class Expression implements Stringable
{
    public function __construct(
        public string $expression
    ) {
    }

    public function __toString(): string
    {
        return $this->expression;
    }
}
