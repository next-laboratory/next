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

namespace Max\Http;

use Max\Di\Annotations\Inject;
use Max\Http\Message\Response as Psr7Response;
use Max\Http\Message\ServerRequest;
use Max\Http\Message\Stream\FileStream;
use Max\Http\Message\Stream\StringStream;
use Max\Server\Events\OnRequest;
use Max\Utils\Context;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\{Request, Response};
use Throwable;

class Server
{
    #[Inject]
    protected ServerRequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    #[Inject]
    protected RequestHandlerInterface $requestHandler;

    /**
     * Request事件回调
     *
     * @param Request  $request
     * @param Response $response
     */
    public function request(Request $request, Response $response)
    {
        try {
            $start = microtime(true);
            Context::put(ServerRequestInterface::class, ServerRequest::createFromSwooleRequest($request));
            Context::put(ResponseInterface::class, new Psr7Response());
            $psr7Response = $this->requestHandler->handle($this->request);
            $this->eventDispatcher?->dispatch(new OnRequest($this->request, $psr7Response, microtime(true) - $start));
            $response->status($psr7Response->getStatusCode());
            $this->setCookie($psr7Response, $response);
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
        } catch (Throwable $throwable) {
            $response->end($throwable->getMessage());
        } finally {
            Context::delete();
        }
    }

    /**
     * @param ResponseInterface $psr7Response
     * @param Response          $response
     */
    protected function setCookie(ResponseInterface &$psr7Response, Response $response)
    {
        $cookies = [];
        foreach ($psr7Response->getHeader('Set-Cookie') as $str) {
            $cookies[] = Cookie::parse($str);
        }
        $psr7Response = $psr7Response->withoutHeader('Set-Cookie');
        if (method_exists($psr7Response, 'getCookies')) {
            $cookies = [...$psr7Response->getCookies()];
        }
        foreach ($cookies as $cookie) {
            $response->cookie(
                $cookie->getName(), $cookie->getValue(),
                $cookie->getExpires(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(),
                $cookie->isHttponly(), $cookie->getSamesite()
            );
        }
    }
}
