<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

use Max\Exceptions\VarDumperAbort;

if (function_exists('d') === false) {
    /**
     * @throws VarDumperAbort
     */
    function d(mixed ...$var)
    {
        throw new VarDumperAbort($var);
    }
}
