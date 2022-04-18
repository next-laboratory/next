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

namespace App\Controllers;

use Max\WebSocket\Annotations\WebSocketHandler;
use Max\WebSocket\Contracts\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

#[WebSocketHandler(path: '/')]
class IndexController implements WebSocketHandlerInterface
{
    /**
     * @param Server $server
     * @param Request $request
     * @return void
     */
    public function onOpen(Server $server, Request $request)
    {
        $server->push($request->fd, __FUNCTION__);
    }

    /**
     * @param Server $server
     * @param Frame $frame
     * @return void
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $server->push($frame->fd, __FUNCTION__);
    }

    /**
     * @param Server $server
     * @param int $fd
     * @return void
     */
    public function onClose(Server $server, int $fd)
    {
        $server->push($fd, __FUNCTION__);
    }
}
