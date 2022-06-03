<?php

namespace Max\HttpServer\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null): void;
}
