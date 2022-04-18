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

use Max\Http\Server as HttpServer;
use Max\Server\Listeners\ServerListener;
use Max\Server\Server;
use Max\WebSocket\Server as WebSocketServer;
use Swoole\Constant;

return [
    'servers'  => [
        [
            'name'      => 'websocket',
            'type'      => Server::SERVER_WEBSOCKET,
            'host'      => '0.0.0.0',
            'port'      => 8080,
            'sockType'  => SWOOLE_SOCK_TCP,
            'settings'  => [
                Constant::OPTION_OPEN_HTTP_PROTOCOL => true,
            ],
            'callbacks' => [
                ServerListener::EVENT_OPEN    => [WebSocketServer::class, 'onOpen'],
                ServerListener::EVENT_MESSAGE => [WebSocketServer::class, 'onMessage'],
                ServerListener::EVENT_CLOSE   => [WebSocketServer::class, 'onClose'],
                ServerListener::EVENT_REQUEST => [HttpServer::class, 'onRequest'],
            ],
        ],
    ],
    'mode'     => SWOOLE_PROCESS,
    'settings' => [
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM       => 4,
    ]
];
