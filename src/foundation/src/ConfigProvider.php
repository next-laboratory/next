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
                'Psr\SimpleCache\CacheInterface'          => 'Max\Foundation\Cache\Cache',
                'Psr\Container\ContainerInterface'        => 'Max\Di\Container',
            ]
        ];
    }

    /**
     * publish
     */
    public function publish()
    {
        $to = dirname(__DIR__, 4) . '/config/http.php';
        file_exists($to) || copy(__DIR__ . '/../publish/http.php', $to);
    }
}
