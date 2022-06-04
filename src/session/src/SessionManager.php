<?php

namespace Max\Session;

use Max\Config\Contracts\ConfigInterface;

class SessionManager
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('session');
    }

    /**
     * @param string|null $name
     *
     * @return Session
     */
    public function create(?string $name = null): Session
    {
        $name    ??= $this->config['default'];
        $config  = $this->config['stores'][$name];
        $handler = $config['handler'];
        $options = $config['options'];
        return new Session(new $handler($options));
    }
}
