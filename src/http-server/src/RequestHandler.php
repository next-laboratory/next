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
    protected array                    $middlewares    = [];
    protected ?RequestHandlerInterface $requestHandler = null;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($middleware = array_shift($this->middlewares)) {
            return $middleware->process($request, $this);
        }

        if (is_null($this->requestHandler)) {
            throw new NotFoundException(404, 'Not Found');
        }

        return $this->requestHandler->handle($request);
    }

    public function withHandler(RequestHandlerInterface $requestHandler): static
    {
        if ($requestHandler instanceof self) {
            throw new InvalidArgumentException('Request handler must not be a MiddlewareHandler instance');
        }
        $this->requestHandler = $requestHandler;

        return $this;
    }

    public function withMiddleware(MiddlewareInterface ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);

        return $this;
    }
}
