<?php

namespace Next\Http\Server;

use InvalidArgumentException;
use Next\Http\Server\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    /** @var array<MiddlewareInterface> */
    protected array                    $middlewares = [];
    protected ?RequestHandlerInterface $handler     = null;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = array_shift($this->middlewares)) {
            return $middleware->process($request, $this);
        }

        if (is_null($this->handler)) {
            throw new NotFoundException(404, 'Not Found');
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
