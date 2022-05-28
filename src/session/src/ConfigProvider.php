<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                ]
            ],
        ];
    }
}
