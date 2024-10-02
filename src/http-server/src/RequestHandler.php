<?php

namespace Next\Http\Server;

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
            throw new NotFoundException('Not Found', 404);
        }

        return $this->requestHandler->handle($request);
    }

    public function setRequestHandler(RequestHandlerInterface $requestHandler): static
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    public function withMiddleware(MiddlewareInterface ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);

        return $this;
    }
}
