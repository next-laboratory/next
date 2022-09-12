<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\Middleware;

use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Contract\StatusCodeInterface;
use Max\Http\Message\Exception\HttpException;
use Max\Http\Message\Response;
use Max\Http\Server\Contract\Renderable;
use Max\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ExceptionHandleMiddleware implements MiddlewareInterface
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReport = [];

    /**
     * @throws Throwable
     */
    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            if (!$this->shouldntReport($e)) {
                $this->report($e, $request);
            }

            return $this->render($e, $request);
        }
    }

    /**
     * 报告异常.
     */
    protected function report(Throwable $e, ServerRequestInterface $request): void
    {
    }

    /**
     * 将异常转为ResponseInterface对象
     */
    protected function render(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if ($e instanceof Renderable) {
            return $e->render($request);
        }
        $message    = $e->getMessage();
        $statusCode = $this->getStatusCode($e);
        if (str_contains($request->getHeaderLine(HeaderInterface::HEADER_ACCEPT), 'application/json')
            || strcasecmp('XMLHttpRequest', $request->getHeaderLine('X-REQUESTED-WITH')) === 0) {
            return new Response($statusCode, [], json_encode([
                'status'  => false,
                'code'    => $statusCode,
                'data'    => $e->getTrace(),
                'message' => $message,
            ], JSON_UNESCAPED_UNICODE));
        }
        return new Response($statusCode, [], sprintf(
            '<html lang="zh"><head><title>%s</title></head><body><pre style="font-size: 1.5em; white-space: break-spaces"><p><b>%s</b></p><b>Stack Trace</b><br>%s</pre></body></html>',
            $message,
            $message,
            $e->getTraceAsString(),
        ));
    }

    protected function getStatusCode(Throwable $e)
    {
        return $e instanceof HttpException ? $e->getCode() : StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }

    /**
     * 忽略报告的异常
     */
    protected function shouldntReport(Throwable $e): bool
    {
        return !is_null(Arr::first($this->dontReport, fn($type) => $e instanceof $type));
    }

    /**
     * 运行环境是否是cli
     */
    protected function runInConsole(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
