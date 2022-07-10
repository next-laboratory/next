<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'publish' => [
                [
                    'name'        => 'database',
                    'source'      => __DIR__ . '/../publish/database.php',
                    'destination' => dirname(__DIR__, 4) . '/config/database.php',
                ],
            ],
        ];
    }
}
