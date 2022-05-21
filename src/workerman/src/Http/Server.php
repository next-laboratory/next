<?php

namespace Max\Workerman\Http;

use Max\Di\Context;
use Max\Http\Message\Cookie;
use Max\Http\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

class Server
{
    public function __construct(protected ResponseEmitter $responseEmitter)
    {
    }

    /**
     * @param TcpConnection $tcpConnection
     * @param Request       $request
     *
     * @return void
     */
    public function onMessage(TcpConnection $tcpConnection, Request $request): void
    {
        try {
            $psrRequest = ServerRequest::createFromWorkermanRequest($request);
            \Max\Context\Context::put(ServerRequestInterface::class, $psrRequest);
            \Max\Context\Context::put(Request::class, $request);
            \Max\Context\Context::put(\Psr\Http\Message\ResponseInterface::class, new \Max\Http\Message\Response());
            $requestHandler = Context::getContainer()->make(\Psr\Http\Server\RequestHandlerInterface::class);
            $psr7Response   = $requestHandler->handle(Context::getContainer()->make(ServerRequestInterface::class));
            $this->responseEmitter->emit($psr7Response, $tcpConnection);
        } catch (Throwable $throwable) {
            var_dump($throwable);
        } finally {
            \Max\Context\Context::delete();
        }
    }
}
