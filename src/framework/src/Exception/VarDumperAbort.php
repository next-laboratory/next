<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Exception;

use Exception;

class VarDumperAbort extends Exception
{
    public function __construct(
        public mixed $vars
    ) {
    }
}
