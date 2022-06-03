<?php

namespace Max\Swoole\Http;

use Max\Http\Contracts\ResponseEmitterInterface;
use Max\Http\Message\Cookie;
use Max\Http\Message\Stream\FileStream;
use Max\Http\Message\Stream\StringStream;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param Response $response
     */
    public function emit(ResponseInterface $psr7Response, $response = null)
    {
        $response->status($psr7Response->getStatusCode());
        foreach ($psr7Response->getHeader('Set-Cookie') as $str) {
            $this->sendCookie(Cookie::parse($str), $response);
        }
        $psr7Response = $psr7Response->withoutHeader('Set-Cookie');

        foreach ($psr7Response->getHeaders() as $name => $value) {
            $response->header($name, $value);
        }
        $body = $psr7Response->getBody();
        switch (true) {
            case $body instanceof FileStream:
                $response->sendfile($body->getMetadata('uri'));
                break;
            case $body instanceof StringStream:
                $response->end($body->getContents());
                break;
            default:
                $response->end();
        }
        $body?->close();
    }

    protected function sendCookie(Cookie $cookie, Response $response): void
    {
        $response->cookie(
            $cookie->getName(), $cookie->getValue(),
            $cookie->getExpires(), $cookie->getPath(),
            $cookie->getDomain(), $cookie->isSecure(),
            $cookie->isHttponly(), $cookie->getSamesite()
        );
    }
}
