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
use Max\Http\Server\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;

class WorkermanResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param ResponseInterface $psrResponse
     * @param TcpConnection $sender
     *
     * @return void
     */
    public function emit(ResponseInterface $psrResponse, $sender = null): void
    {
        try {
            $response = new Response($psrResponse->getStatusCode());
            /** @var string[] $cookies */
            foreach ($psrResponse->getHeader('Set-Cookie') as $cookie) {
                $cookie = Cookie::parse($cookie);
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
            $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
            foreach ($psrResponse->getHeaders() as $name => $values) {
                $response->header($name, implode(', ', $values));
            }
            $body = $psrResponse->getBody();
            $content = (string)$body?->getContents();
            $body?->close();
            $sender->send($response->withBody($content));
            $sender->close();
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage() . PHP_EOL;
        }
    }
}
