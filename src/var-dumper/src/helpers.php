<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

use Max\VarDumper\Abort;

if (function_exists('d') === false) {
    /**
     * @throws Abort
     */
    function d(...$vars)
    {
        throw new Abort($vars);
    }
}

if (function_exists('dd') === false) {
    /**
     * Use `d` instead of `dd`
     *
     * @throws ErrorException
     * @deprecated
     */
    function dd(...$vars)
    {
        throw new ErrorException('Use `d` instead of `dd`');
    }
}
