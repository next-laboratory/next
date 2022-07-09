<?php

use Max\Framework\Exceptions\VarDumperAbort;

if (false === function_exists('d')) {
    /**
     * @throws VarDumperAbort
     */
    function d(mixed ...$var)
    {
        throw new VarDumperAbort($var);
    }
}
