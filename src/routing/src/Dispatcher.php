<?php

namespace Next\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

    }
}