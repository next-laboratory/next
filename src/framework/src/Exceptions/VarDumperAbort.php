<?php

namespace Max\Framework\Exceptions;

use Exception;

class VarDumperAbort extends Exception
{
    public function __construct(public mixed $var)
    {
    }
}
