<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session;

use Max\Config\Contract\ConfigInterface;
use SessionHandlerInterface;

class Manager
{
    protected SessionHandlerInterface $sessionHandler;

    public function __construct(ConfigInterface $config)
    {
        $config               = $config->get('session');
        $handler              = $config['handler'];
        $config               = $config['config'];
        $this->sessionHandler = new $handler($config);
    }

    /**
     * 建立新的会话
     */
    public function create(): Session
    {
        return new Session($this->sessionHandler);
    }
}
