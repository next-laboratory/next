<?php

namespace Max\Session;

use Max\Config\Contracts\ConfigInterface;

class SessionManager
{
    /**
     * Session config.
     */
    protected array $config = [];

    /**
     * Session handlers.
     */
    protected array $handlers = [];

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('session');
    }

    /**
     * Create a new session.
     */
    public function create(?string $name = null): Session
    {
        $name ??= $this->config['default'];
        if (!isset($this->handlers[$name])) {
            $config                = $this->config['stores'][$name];
            $handler               = $config['handler'];
            $options               = $config['options'];
            $this->handlers[$name] = new $handler($options);
        }

        return new Session($this->handlers[$name]);
    }
}
