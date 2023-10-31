<?php

namespace Next\Http\Server\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpKernelInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}