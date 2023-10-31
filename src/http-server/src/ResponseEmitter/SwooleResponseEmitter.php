<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server\ResponseEmitter;

use Next\Http\Message\Cookie;
use Next\Http\Message\Stream\FileStream;
use Next\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class SwooleResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param Response $sender
     */
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        $sender->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());
        foreach ($psrResponse->getHeader('Set-Cookie') as $cookieLine) {
            $cookie = Cookie::parse($cookieLine);
            $sender->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttponly(),
                $cookie->getSameSite()
            );
        }
        $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
        foreach ($psrResponse->getHeaders() as $key => $value) {
            $sender->header($key, implode(', ', $value));
        }
        $body = $psrResponse->getBody();
        switch (true) {
            case $body instanceof FileStream:
                $sender->sendfile($body->getFilename(), $body->getOffset(), $body->getLength());
                break;
            default:
                $sender->end($body?->getContents());
        }
        $body?->close();
    }
}
