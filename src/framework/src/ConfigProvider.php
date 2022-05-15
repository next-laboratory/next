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

namespace Max\Framework;

class ConfigProvider
{
    /**
     * @return string[][]
     */
    public function __invoke(): array
    {
        return [
            'commands' => [
                'Max\Framework\Console\Commands\Make',
                'Max\Framework\Console\Commands\Queue',
                'Max\Framework\Console\Commands\RouteList',
                'Max\Framework\Console\Commands\Swoole',
                'Max\Framework\Console\Commands\VendorPublish',
                'Max\Framework\Console\Commands\Workerman',
                'Max\Framework\Console\Commands\Swagger',
            ]
        ];
    }
}
