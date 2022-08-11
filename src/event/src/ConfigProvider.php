<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Event;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'bindings' => [
                'Psr\EventDispatcher\ListenerProviderInterface' => 'Max\Event\ListenerProvider',
                'Psr\EventDispatcher\EventDispatcherInterface'  => 'Max\Event\EventDispatcher',
                'Max\Event\Contract\EventDispatcherInterface'   => 'Max\Event\EventDispatcher',
            ],
        ];
    }
}
