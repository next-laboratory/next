<?php

namespace Next\Http\Server;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class RequestHandler implements RequestHandlerInterface
{
    /** @var array<MiddlewareInterface> */
    protected array                   $middlewares = [];
    protected RequestHandlerInterface $handler;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = current($this->middlewares)) {
            next($this->middlewares);
            return $middleware->process($request, $this);
        }

        if (!isset($this->handler)) {
            throw new RuntimeException('Handler has not bee set');
        }

        return $this->handler->handle($request);
    }

    public function withHandler(RequestHandlerInterface $handler): static
    {
        if ($handler instanceof self) {
            throw new InvalidArgumentException('Handler must not be an instance of RequestHandler');
        }
        $this->handler = $handler;

        return $this;
    }

    public function withMiddleware(MiddlewareInterface ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);

        return $this;
    }
}
