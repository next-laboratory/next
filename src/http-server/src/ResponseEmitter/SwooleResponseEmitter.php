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

namespace Max\Http\Server\ResponseEmitter;

use Max\Http\Message\Cookie;
use Max\Http\Message\Stream\FileStream;
use Max\Http\Message\Stream\StringStream;
use Max\Http\Server\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class SwooleResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param ResponseInterface $psrResponse
     * @param Response $sender
     *
     * @return void
     */
    public function emit(ResponseInterface $psrResponse, $sender = null): void
    {
        try {
            $sender->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());
            foreach ($psrResponse->getHeader('Set-Cookie') as $cookie) {
                $this->sendCookie(Cookie::parse($cookie), $sender);
            }
            $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
            foreach ($psrResponse->getHeaders() as $key => $value) {
                $sender->header($key, implode(', ', $value));
            }
            $body = $psrResponse->getBody();
            switch (true) {
                case $body instanceof FileStream:
                    $sender->sendfile($body->getMetadata('uri'), $body->tell(), max($body->getLength(), 0));
                    break;
                case $body instanceof StringStream:
                    $sender->end($body->getContents());
                    break;
                default:
                    $sender->end();
            }
            $body?->close();
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage() . PHP_EOL;
        }
    }

    /**
     * @param Cookie $cookie
     * @param Response $response
     *
     * @return void
     */
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
