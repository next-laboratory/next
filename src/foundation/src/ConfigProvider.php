<?php
declare(strict_types=1);

namespace Max\Foundation;

class ConfigProvider
{
    /**
     * @return string[][]
     */
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Psr\SimpleCache\CacheInterface'   => 'Max\Cache\Cache',
                'Psr\Container\ContainerInterface' => 'Max\Di\Container',
            ]
        ];
    }

    /**
     * publish
     */
    public function publish()
    {

    }
}
