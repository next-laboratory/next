<?php

namespace Max\Workerman;

use Max\Utils\Traits\AutoFillProperties;

class ServerConfig
{
    use AutoFillProperties;

    protected string $listen = '';
    protected array $callbacks = [];
    protected array $settings = [];

    public function __construct(array $config)
    {
        $this->fillProperties($config);
    }

    /**
     * @return string
     */
    public function getListen(): string
    {
        return $this->listen;
    }

    /**
     * @return array
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}