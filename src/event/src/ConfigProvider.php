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

namespace Max\Event;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'bindings' => [
                'Psr\EventDispatcher\ListenerProviderInterface' => 'Max\Event\ListenerProvider',
                'Psr\EventDispatcher\EventDispatcherInterface'  => 'Max\Event\EventDispatcher',
                'Max\Event\Contracts\EventDispatcherInterface'  => 'Max\Event\EventDispatcher',
            ],
        ];
    }
}
