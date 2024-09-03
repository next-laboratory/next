<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

use Next\VarDumper\Dumper;

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
     * Use `d` instead of `dd`.
     *
     * @deprecated
     */
    function dd(...$vars): void
    {
        d(...$vars);
    }
}
