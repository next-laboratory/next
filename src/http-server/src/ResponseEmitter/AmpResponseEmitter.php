<?php

namespace Max\Http\Server\ResponseEmitter;

use Amp\Http\Server\Response;
use Max\Http\Server\Contracts\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class AmpResponseEmitter implements ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        return new Response($psrResponse->getStatusCode(), $psrResponse->getHeaders(), $psrResponse->getBody());
    }
}
