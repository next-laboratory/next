<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Query;

class Expression
{
    public function __construct(
        public string $expression
    ) {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}
