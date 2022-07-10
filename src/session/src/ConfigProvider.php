<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Session;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'name'        => 'session',
                    'source'      => __DIR__ . '/../publish/session.php',
                    'destination' => dirname(__DIR__, 4) . '/config/session.php',
                ],
            ],
        ];
    }
}
