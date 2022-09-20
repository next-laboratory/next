<?php

namespace Max\Event;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Psr\EventDispatcher\EventDispatcherInterface'  => 'Max\Event\EventDispatcher',
                'Psr\EventDispatcher\ListenerProviderInterface' => 'Max\Event\ListenerProvider',
            ],
        ];
    }
}
