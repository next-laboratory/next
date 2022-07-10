<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Framework\Exceptions\Handlers;

use ErrorException;
use Max\Framework\Exceptions\VarDumperAbort;
use Max\Http\Message\Response;
use Max\Http\Message\Stream\StringStream;
use Max\Http\Server\Contracts\ExceptionHandlerInterface;
use Max\Http\Server\Contracts\StoppableExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;

class VarDumperAbortHandler implements ExceptionHandlerInterface, StoppableExceptionHandlerInterface
{
    /**
     * @param VarDumperAbort $throwable
     *
     * @throws ErrorException
     */
    public function handle(Throwable $throwable, ServerRequestInterface $request): ?ResponseInterface
    {
        ob_start();
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
        foreach ($throwable->vars as $var) {
            (new HtmlDumper())->dump($cloner->cloneVar($var));
        }

        return new Response(body: new StringStream(ob_get_clean()));
    }

    public function isValid(Throwable $throwable): bool
    {
        return class_exists('Symfony\Component\VarDumper\Dumper\HtmlDumper') && $throwable instanceof VarDumperAbort;
    }
}
