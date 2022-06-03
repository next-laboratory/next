<?php

namespace Max\HttpServer\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ExceptionHandlerInterface
{
    public function handleException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface;
}
