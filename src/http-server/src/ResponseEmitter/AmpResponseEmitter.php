<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\ResponseEmitter;

use Amp\Http\Server\Response;
use Max\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class AmpResponseEmitter implements ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        return new Response($psrResponse->getStatusCode(), $psrResponse->getHeaders(), $psrResponse->getBody());
    }
}
