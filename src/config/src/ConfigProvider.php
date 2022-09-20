<?php

namespace Max\Config;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Max\Config\Contract\ConfigInterface' => 'Max\Config\Repository',
            ]
        ];
    }
}
