<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session;

class SessionManager
{
    /**
     * Session handlers.
     */
    protected array $handlers = [];

    public function __construct(
        protected array $config = []
    ) {
    }

    /**
     * Create a new session.
     */
    public function create(?string $name = null): Session
    {
        $name ??= $this->config['default'];
        if (! isset($this->handlers[$name])) {
            $config                = $this->config['stores'][$name];
            $handler               = $config['handler'];
            $options               = $config['options'];
            $this->handlers[$name] = new $handler($options);
        }

        return new Session($this->handlers[$name]);
    }
}
