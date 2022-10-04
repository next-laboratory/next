<?php

namespace Max\Console;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'commands' => [
                'Max\Console\Command\ControllerMakeCommand',
                'Max\Console\Command\MiddlewareMakeCommand',
                'Max\Console\Command\RouteListCommand',
            ]
        ];
    }
}
