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

namespace Max\HttpServer\ResponseEmitter;

use Max\Http\Message\Cookie;
use Max\HttpServer\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class FPMResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param ResponseInterface $psrResponse
     * @param                   $sender
     *
     * @return void
     */
    public function emit(ResponseInterface $psrResponse, $sender = null): void
    {
        header(sprintf('HTTP/%s %d %s', $psrResponse->getProtocolVersion(), $psrResponse->getStatusCode(), $psrResponse->getReasonPhrase()), true);
        foreach ($psrResponse->getHeader('Set-Cookie') as $cookieString) {
            $cookie = Cookie::parse($cookieString);
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttponly()
            );
        }
        $psrResponse = $psrResponse->withoutHeader('Set-Cookie');
        foreach ($psrResponse->getHeaders() as $name => $value) {
            header($name . ': ' . implode(', ', $value));
        }
        $body = $psrResponse->getBody();
        echo $body?->getContents();
    }
}
