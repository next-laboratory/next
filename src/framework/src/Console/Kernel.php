<?php

namespace Next\Console;

use Next\Console\Command\ControllerMakeCommand;
use Next\Console\Command\MiddlewareMakeCommand;
use Next\Console\Command\RouteListCommand;
use Symfony\Component\Console\Application;

class Kernel extends Application
{
    private array $commands = [
        RouteListCommand::class,
        ControllerMakeCommand::class,
        MiddlewareMakeCommand::class,
    ];

    public function __construct(
        string $name = 'UNKNOWN',
        string $version = 'UNKNOWN'
    )
    {
        parent::__construct($name, $version);
        foreach (array_merge($this->commands, $this->commands()) as $command) {
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
