<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server\ResponseEmitter;

use Next\Http\Message\Cookie;
use Next\Http\Message\Stream\FileStream;
use Next\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;

class WorkerManResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param TcpConnection $sender
     */
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        $response    = new Response($psrResponse->getStatusCode());
        $cookies     = $psrResponse->getHeader('Set-Cookie');
        $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
        foreach ($psrResponse->getHeaders() as $name => $values) {
            $response->header($name, implode(', ', $values));
        }
        $body = $psrResponse->getBody();
        if ($body instanceof FileStream) {
            $sender->send($response->withFile($body->getFilename(), $body->getOffset(), $body->getLength()));
        } else {
            foreach ($cookies as $cookie) {
                $cookie = Cookie::parse($cookie);
                $response->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getMaxAge(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttponly(),
                    $cookie->getSameSite()
                );
            }
            $sender->send($response->withBody((string)$body?->getContents()));
        }
        $body?->close();
        $sender->close();
    }
}
