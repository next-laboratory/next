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

namespace Max\WebSocket;

use Max\Event\EventDispatcher;
use Max\Server\Events\OnClose;
use Max\Server\Events\OnMessage;
use Max\Server\Events\OnOpen;
use Max\WebSocket\Contracts\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as SwooleServer;

class Server
{
    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(protected ?EventDispatcher $eventDispatcher = null)
    {
    }

    /**
     * HandShake
     *
     * @param Request $request
     * @param Response $response
     */
    public function onHandShake(Request $request, Response $response)
    {
    }

    /**
     * @param SwooleServer $server
     * @param Request $request
     */
    public function onOpen(SwooleServer $server, Request $request)
    {
        $path = $request->server['request_uri'];
        /** @var WebSocketHandlerInterface $handler */
        if ($handler = RouteCollector::getRoute($path)) {
            Context::put($request->fd, RouteCollector::class, $path);
            $handler->onOpen($server, $request);
            $this->eventDispatcher?->dispatch(new OnOpen($server, $request));
        } else {
            $server->push($request->fd, 'Not Found.');
            $server->close($request->fd);
        }
    }

    /**
     * @param SwooleServer $server
     * @param Frame $frame
     */
    public function onMessage(SwooleServer $server, Frame $frame)
    {
        if ($server->isEstablished($frame->fd) && $handler = $this->getHandler($frame->fd)) {
            $handler->onMessage($server, $frame);
            $this->eventDispatcher?->dispatch(new OnMessage($server, $frame));
        }
    }

    /**
     * @param \Swoole\Server $server
     * @param                          $fd
     */
    public function onClose(\Swoole\Server $server, $fd)
    {
        if ($handler = $this->getHandler($fd)) {
            $handler->onClose($server, $fd);
        }
        $this->eventDispatcher?->dispatch(new OnClose($server, $fd));
        Context::delete($fd);
    }

    /**
     * @param SwooleServer $server
     * @param $fd
     * @return void
     */
    public function onDisconnect(SwooleServer $server, $fd)
    {
        Context::delete($fd);
    }

    /**
     * 获取注册的Handler
     *
     * @param $fd
     *
     * @return WebSocketHandlerInterface|null
     */
    protected function getHandler($fd): ?WebSocketHandlerInterface
    {
        if ($path = Context::get($fd, RouteCollector::class)) {
            return RouteCollector::getRoute($path);
        }
        return null;
    }
}
