<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Console\Exceptions;

use Exception;

class InvalidOptionException extends Exception
{
    public function __construct($message)
    {
        parent::__construct("\033[41;30mInvalidOption!\033[0m\n{$message}", $this->getCode(), $this->getPrevious());
    }
}
