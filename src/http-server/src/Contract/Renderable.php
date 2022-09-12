<?php

namespace Max\Http\Server\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Renderable
{
    public function render(ServerRequestInterface $request): ResponseInterface;
}
