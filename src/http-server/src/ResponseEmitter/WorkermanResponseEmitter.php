<?php

namespace Max\HttpServer\ResponseEmitter;

use Max\Http\Message\Cookie;
use Max\HttpServer\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;

class WorkermanResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param TcpConnection $tcpConnection
     */
    public function emit(ResponseInterface $psrResponse, $tcpConnection = null)
    {
        $response = new Response($psrResponse->getStatusCode());
        foreach ($psrResponse->getHeaders() as $name => $values) {
            if (0 === strcasecmp('Set-Cookie', $name)) {
                foreach ($values as $value) {
                    $cookie = Cookie::parse($value);
                    $response->cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getMaxAge(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->isSecure(),
                        $cookie->isHttponly(),
                        $cookie->getSamesite()
                    );
                }
            } else {
                $response->header($name, implode(', ', $values));
            }
        }

        $body    = $psrResponse->getBody();
        $content = (string)$body?->getContents();
        $body?->close();
        $tcpConnection->send($response->withBody($content));
    }
}
