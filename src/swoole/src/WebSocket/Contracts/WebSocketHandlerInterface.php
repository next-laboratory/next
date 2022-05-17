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

namespace Max\Swoole\WebSocket\Contracts;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocketHandlerInterface
{
    public function onOpen(Server $server, Request $request);

    public function onMessage(Server $server, Frame $frame);

    public function onClose(Server $server, int $fd);
}
