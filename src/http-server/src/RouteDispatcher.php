<?php

namespace Next\Http\Server;

use Next\Routing\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatcher implements RequestHandlerInterface
{
    public function __construct(
        protected Route $route,
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func_array($this->route->getAction(), [$request]);
    }
}
