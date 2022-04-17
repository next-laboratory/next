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

use Max\Http\Server;
use Max\Server\Listeners\ServerListener;
use Swoole\Constant;

return [
    'servers'   => [
        [
            'name'      => 'websocket',
            'type'      => \Max\Server\Server::SERVER_WEBSOCKET,
            'host'      => '0.0.0.0',
            'port'      => 9501,
            'sockType'  => SWOOLE_SOCK_TCP,
            'settings'  => [
                Constant::OPTION_OPEN_WEBSOCKET_PROTOCOL => true,
            ],
            'callbacks' => [
                ServerListener::EVENT_OPEN    => [\Max\WebSocket\Server::class, 'OnOpen'],
                ServerListener::EVENT_MESSAGE => [\Max\WebSocket\Server::class, 'OnMessage'],
                ServerListener::EVENT_CLOSE   => [\Max\WebSocket\Server::class, 'OnClose'],
                ServerListener::EVENT_RECEIVE => [\Max\WebSocket\Server::class, 'OnReceive']
            ],
        ],
        [
            'name'      => 'http',
            'type'      => \Max\Server\Server::SERVER_HTTP,
            'host'      => '0.0.0.0',
            'port'      => 8080,
            'sockType'  => SWOOLE_SOCK_TCP,
            'settings'  => [
                Constant::OPTION_OPEN_HTTP_PROTOCOL => true,
            ],
            'callbacks' => [
                ServerListener::EVENT_REQUEST => [Server::class, 'onRequest'],
            ],
        ],
    ],
    'mode'      => SWOOLE_BASE,
    'settings'  => [
        Constant::OPTION_ENABLE_COROUTINE      => true,
        Constant::OPTION_TASK_WORKER_NUM       => 2,
        Constant::OPTION_WORKER_NUM            => 4,
        Constant::OPTION_TASK_ENABLE_COROUTINE => true,
    ],
    'callbacks' => [
        ServerListener::EVENT_TASK   => [\Max\Server\Callbacks::class, 'onTask'],
        ServerListener::EVENT_FINISH => [\Max\Server\Callbacks::class, 'onFinish'],
    ],
];
