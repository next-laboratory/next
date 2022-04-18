<?php

namespace App\Controllers;

use Max\WebSocket\Annotations\WebSocketHandler;
use Max\WebSocket\Contracts\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

#[WebSocketHandler(path: '/test')]
class TestController implements WebSocketHandlerInterface
{
    public function onOpen(Server $server, Request $request)
    {
        // TODO: Implement onOpen() method.
    }

    public function onMessage(Server $server, Frame $frame)
    {
        // TODO: Implement onMessage() method.
    }

    public function onClose(Server $server, int $fd)
    {
        // TODO: Implement onClose() method.
    }
}
