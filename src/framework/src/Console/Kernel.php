<?php

namespace Max\Console;

use Symfony\Component\Console\Application;

class Kernel extends Application
{
    public function __construct(
        string $name = 'UNKNOWN',
        string $version = 'UNKNOWN'
    )
    {
        parent::__construct($name, $version);
        foreach ($this->commands() as $command) {
            $this->add(make($command));
        }
    }

    /**
     * 注册命令
     */
    protected function commands(): array
    {
        return [];
    }
}
