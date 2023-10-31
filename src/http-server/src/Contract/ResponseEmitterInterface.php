<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server\Contract;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null);
}
