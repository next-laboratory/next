<?php

namespace Max\Http\Server\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $throwable, ServerRequestInterface $request): ?ResponseInterface;

    public function isValid(Throwable $throwable): bool;
}
