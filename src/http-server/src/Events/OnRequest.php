<?php

namespace Max\HttpServer\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OnRequest
{
    public function __construct(public ServerRequestInterface $request, public ResponseInterface $response)
    {
    }
}
