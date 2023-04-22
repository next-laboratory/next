<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

use Max\VarDumper\Dumper;

if (function_exists('d') === false) {
    /**
     * @throws Dumper
     */
    function d(...$vars)
    {
        throw new Dumper($vars);
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
