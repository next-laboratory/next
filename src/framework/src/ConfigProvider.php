<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
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
                'Max\Framework\Console\Commands\RouteListCommand',
                'Max\Framework\Console\Commands\ControllerMakeCommand',
                'Max\Framework\Console\Commands\MiddlewareMakeCommand',
            ],
        ];
    }
}
