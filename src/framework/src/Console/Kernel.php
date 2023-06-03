<?php

namespace Max\Console;

use Max\Console\Command\ControllerMakeCommand;
use Max\Console\Command\MiddlewareMakeCommand;
use Max\Console\Command\RouteListCommand;
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
        foreach (array_merge($this->commands, $this->commands(), CommandCollector::all()) as $command) {
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
