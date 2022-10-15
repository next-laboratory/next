<?php

namespace Max;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                'Max\Console\Command\ControllerMakeCommand',
                'Max\Console\Command\MiddlewareMakeCommand',
                'Max\Console\Command\RouteListCommand',
            ],
        ];
    }
}
