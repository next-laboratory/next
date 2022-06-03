<?php

namespace Max\HttpServer;

use Max\Http\Exceptions\HttpException;
use Max\Http\Message\Response;
use Max\HttpServer\Contracts\ExceptionHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @throws Throwable
     */
    public function handleException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $this->reportException($throwable, $request);

        return $this->renderException($throwable, $request);
    }

    protected function reportException(Throwable $throwable, ServerRequestInterface $request): void
    {
        var_dump($throwable, $request);
    }

    /**
     * @throws Throwable
     */
    protected function renderException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $statusCode = $throwable instanceof HttpException ? $throwable->getCode() : 400;

        return new Response($statusCode, [], $throwable->getMessage());
    }
}
