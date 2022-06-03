<?php

namespace Max\Workerman\Http;

use Max\Http\Contracts\ResponseEmitterInterface;
use Max\Http\Message\Cookie;
use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;

class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param TcpConnection $tcpConnection
     */
    public function emit(ResponseInterface $psr7Response, $tcpConnection = null)
    {
        $response = new Response($psr7Response->getStatusCode());
        foreach ($psr7Response->getHeaders() as $name => $values) {
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

        $body    = $psr7Response->getBody();
        $content = (string)$body?->getContents();
        $body?->close();
        $tcpConnection->send($response->withBody($content));
    }
}
