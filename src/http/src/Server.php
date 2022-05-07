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

use Max\Http\Message\Response as Psr7Response;
use Max\Http\Message\ServerRequest;
use Max\Http\Message\Stream\FileStream;
use Max\Http\Message\Stream\StringStream;
use Max\Server\Events\OnRequest;
use Max\Context\Context;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\{Request, Response};
use Throwable;

class Server
{
    /**
     * @param ServerRequestInterface        $request
     * @param RequestHandlerInterface       $requestHandler
     * @param EventDispatcherInterface|null $eventDispatcher
     * @param LoggerInterface|null          $logger
     */
    public function __construct(
        protected ServerRequestInterface    $request,
        protected RequestHandlerInterface   $requestHandler,
        protected ?EventDispatcherInterface $eventDispatcher = null,
        protected ?LoggerInterface          $logger = null,
    )
    {
    }

    /**
     * Request事件回调
     *
     * @param Request  $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response): void
    {
        try {
            $start = microtime(true);
            Context::put(ServerRequestInterface::class, ServerRequest::createFromSwooleRequest($request));
            Context::put(ResponseInterface::class, new Psr7Response());
            Context::put(Request::class, $request);
            Context::put(Response::class, $response);
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
            $this->logger?->error(sprintf('%s:%s in %s +%d',
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine()
            ), $throwable->getTrace());
            $response->header('Content-Type', 'application/json');
            $response->end('{"code": 500, "message": "Internal error."}');
        } finally {
            Context::delete();
        }
    }

    /**
     * @param ResponseInterface $psr7Response
     * @param Response          $response
     */
    protected function setCookie(ResponseInterface &$psr7Response, Response $response): void
    {
        if (method_exists($psr7Response, 'getCookies') && $cookies = $psr7Response->getCookies()) {
            foreach ($cookies as $cookie) {
                $this->sendCookie($cookie, $response);
            }
        }

        foreach ($psr7Response->getHeader('Set-Cookie') as $str) {
            $this->sendCookie(Cookie::parse($str), $response);
        }
        $psr7Response = $psr7Response->withoutHeader('Set-Cookie');
    }

    /**
     * @param Cookie   $cookie
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
