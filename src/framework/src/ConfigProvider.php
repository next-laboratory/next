<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max;

class ConfigProvider
{
    /**
     * @return string[][]
     */
    public function __invoke(): array
    {
        return [
            'commands' => [
                'Max\Console\Command\RouteListCommand',
                'Max\Console\Command\ControllerMakeCommand',
                'Max\Console\Command\MiddlewareMakeCommand',
            ],
        ];
    }
}
