<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Config;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'bindings' => [
                'Max\Config\Contract\ConfigInterface' => 'Max\Config\Repository',
            ],
        ];
    }
}
