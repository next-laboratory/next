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

namespace Max\Swoole\Http;

use Max\Context\Context;
use Max\Http\Message\Response as Psr7Response;
use Max\Http\Message\ServerRequest;
use Max\Swoole\Events\OnRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Http\{Request, Response};
use Throwable;

class Server
{
    public function __construct(
        protected ServerRequestInterface    $request,
        protected RequestHandlerInterface   $requestHandler,
        protected ResponseEmitter           $responseEmitter,
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
            $this->responseEmitter->emit($psr7Response, $response);
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
}
