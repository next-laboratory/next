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
use Max\VarDumper\Dumper;
use Max\VarDumper\DumperHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HyperfDumperHandler extends ExceptionHandler
{
    use DumperHandler;

    /**
     * @param Dumper $e
     *
     * @throws ErrorException
     */
    public function handle(Throwable $e, ResponseInterface $response)
    {
        $this->stopPropagation();

        return $response->withBody(new SwooleStream(self::convertToHtml($e)));
    }

    public function isValid(Throwable $e): bool
    {
        return $e instanceof Dumper;
    }
}
