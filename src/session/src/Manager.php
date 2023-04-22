<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session;

use SessionHandlerInterface;

class Manager
{
    public function __construct(
        protected SessionHandlerInterface $sessionHandler
    )
    {
    }

    /**
     * 建立新的会话
     */
    public function create(): Session
    {
        return new Session($this->sessionHandler);
    }
}
