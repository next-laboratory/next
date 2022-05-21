<?php

namespace Max\Http\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $psr7Response, $response);
}
