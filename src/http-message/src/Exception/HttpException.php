<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message\Exception;

class HttpException extends \RuntimeException
{
    public function __construct(
        protected int $statusCode,
        string        $message = '',
        int           $code = 0,
        \Throwable    $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
