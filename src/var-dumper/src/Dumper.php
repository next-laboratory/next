<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\VarDumper;

use RuntimeException;

class Dumper extends RuntimeException
{
    public array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }
}
