<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\HttpServer;

use Max\HttpMessage\Response;
use Max\HttpServer\Contracts\ExceptionHandlerInterface;
use Max\HttpServer\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @param Throwable              $throwable
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handleException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $this->reportException($throwable, $request);

        return $this->renderException($throwable, $request);
    }

    /**
     * @param Throwable              $throwable
     * @param ServerRequestInterface $request
     *
     * @return void
     */
    protected function reportException(Throwable $throwable, ServerRequestInterface $request): void
    {
    }

    /**
     * @param Throwable              $throwable
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected function renderException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $statusCode = $throwable instanceof HttpException ? $throwable->getCode() : 400;

        return new Response($statusCode, [], $throwable->getMessage());
    }
}
