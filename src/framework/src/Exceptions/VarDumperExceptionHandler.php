<?php

namespace Max\Framework\Exceptions;

use ErrorException;
use Max\Http\Message\Response;
use Max\Http\Message\Stream\StringStream;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class VarDumperExceptionHandler
{
    /**
     * @throws ErrorException
     */
    public static function convertToResponse(VarDumperAbort $abort): ResponseInterface
    {
        ob_start();
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
        (new HtmlDumper())->dump($cloner->cloneVar($abort->var));

        return new Response(body: new StringStream(ob_get_clean()));
    }
}
