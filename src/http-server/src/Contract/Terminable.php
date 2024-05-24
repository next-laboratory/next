<?php

namespace Next\Http\Server\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Terminable
{
    public function terminate(ServerRequestInterface $request, ResponseInterface $response): void;
}