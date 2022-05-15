<?php

namespace Max\Workerman\Http;

use Max\Di\Context;
use Max\Http\Message\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

class Server
{
    public function onMessage(TcpConnection $tcpConnection, Request $request)
    {
        try {
            $psrRequest = ServerRequest::createFromWorkermanRequest($request);
            \Max\Context\Context::put(ServerRequestInterface::class, $psrRequest);
            \Max\Context\Context::put(Request::class, $request);
            \Max\Context\Context::put(\Psr\Http\Message\ResponseInterface::class, new \Max\Http\Message\Response());
            $requestHandler = Context::getContainer()->make(\Psr\Http\Server\RequestHandlerInterface::class);
            $psr7Response = $requestHandler->handle(Context::getContainer()->make(ServerRequestInterface::class));
            $tcpConnection->send($this->convertToRaw($psr7Response), true);
        } catch (Throwable $throwable) {
            dump($throwable);
        } finally {
            \Max\Context\Context::delete();
        }
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    protected function convertToRaw(ResponseInterface $response): string
    {
        $raw = 'HTTP/' . $response->getProtocolVersion() . ' ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . "\r\n";
        $body = $response->getBody();
        $rawBody = (string)$body?->getContents();
        $raw .= "Connection: keep-alive\r\n";
        $raw .= 'Content-Length: ' . $body->getSize() . "\r\n";
        foreach ($response->getHeaders() as $name => $headers) {
            if (0 === strcasecmp('Set-Cookie', $name)) {
                foreach ($headers as $header) {
                    $raw .= $name . ': ' . $header . "\r\n";
                }
            } else {
                $raw .= $name . ': ' . implode(',', $headers) . "\r\n";
            }
        }
        $raw .= "\r\n" . $rawBody;
        $body?->close();
        return $raw;
    }
}