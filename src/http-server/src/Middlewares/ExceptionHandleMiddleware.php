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

namespace Max\Http\Server\Middlewares;

use Max\Http\Message\Response;
use Max\Http\Server\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ExceptionHandleMiddleware implements MiddlewareInterface
{
    protected array $httpExceptions = [
        'Max\Routing\Exceptions\MethodNotAllowedException',
        'Max\Routing\Exceptions\RouteNotFoundException',
    ];

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $this->reportException($throwable, $request);
            return $this->renderException($throwable, $request);
        }
    }

    /**
     * @param Throwable              $throwable
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws Throwable
     */
    final public function handleException(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
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
        $message = sprintf('%s: %s in %s +%d', $throwable::class, $throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
        return new Response($this->getStatusCode($throwable), [], sprintf(<<<EOT
<html><head><title>%s</title></head><body><pre style="font-size: 1.5em; white-space: break-spaces"><p><b>%s</b></p><b>Stack Trace</b><br>%s</pre></body></html>
EOT
                ,
                $message,
                $message,
                $throwable->getTraceAsString(),
            )
        );
    }

    /**
     * @param Throwable $throwable
     *
     * @return int
     */
    protected function getStatusCode(Throwable $throwable): int
    {
        $statusCode = 500;
        if (in_array($throwable::class, $this->httpExceptions) || $throwable instanceof HttpException) {
            $statusCode = $throwable->getCode();
        }
        return (int)$statusCode;
    }
}
