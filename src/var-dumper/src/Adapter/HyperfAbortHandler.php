<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\VarDumper\Adapter;

use ErrorException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Max\VarDumper\Abort;
use Max\VarDumper\AbortHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HyperfAbortHandler extends ExceptionHandler
{
    use AbortHandler;

    /**
     * @param Abort $e
     *
     * @throws ErrorException
     */
    public function handle(Throwable $e, ResponseInterface $response)
    {
        $this->stopPropagation();

        return $response->withBody(new SwooleStream($this->convertToHtml($e)));
    }

    public function isValid(Throwable $e): bool
    {
        return $e instanceof Abort;
    }
}
