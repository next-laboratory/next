<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Server\Middleware;

use Closure;
use Next\Http\Message\Contract\StatusCodeInterface;
use Next\Http\Message\Exception\HttpException;
use Next\Http\Message\Response;
use Next\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandleMiddleware implements MiddlewareInterface
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected array $dontReport = [];

    protected array $renderCallbacks = [];

    protected function renderable(callable $renderUsing)
    {
        if (!$renderUsing instanceof Closure) {
            $renderUsing = $renderUsing(...);
        }

        $this->renderCallbacks[] = $renderUsing;

        return $this;
    }

    /**
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            set_error_handler(function ($errno, $errMsg, $errFile, $errLine) {
                throw new \ErrorException($errMsg, 0, $errno, $errFile, $errLine);
            });
            $response = $handler->handle($request);
            restore_error_handler();
            return $response;
        } catch (\Throwable $e) {
            if (!$this->shouldntReport($e)) {
                $this->report($e, $request);
            }
            return $this->render($e, $request);
        }
    }

    /**
     * 报告异常.
     */
    protected function report(\Throwable $e, ServerRequestInterface $request): void
    {
    }

    /**
     * 将异常转为ResponseInterface对象
     */
    protected function render(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $message    = $e->getMessage();
        $statusCode = $this->getStatusCode($e);
        if (str_contains($request->getHeaderLine('Accept'), 'json')
            || strcasecmp('XMLHttpRequest', $request->getHeaderLine('X-REQUESTED-WITH')) === 0) {
            $body = json_encode([
                'status'  => false,
                'code'    => $statusCode,
                'data'    => $e->getTrace(),
                'message' => $message,
            ], JSON_UNESCAPED_UNICODE);

            return new Response($statusCode, ['Content-Type' => 'application/json; charset=utf-8'], $body);
        }
        $html = <<<'HTML'
<html lang="zh">
    <head>
        <title>%s</title>
        </head>
        <body>
<pre style="white-space: break-spaces">
<b style="color: red">%s %s in %s:%d</b>
<b>Stack Trace</b>
%s
</pre>
        </body>
</html>
HTML;

        $body = sprintf($html, $message, $e::class, $message, $e->getFile(), $e->getLine(), $e->getTraceAsString());

        return new Response($statusCode, ['Content-Type' => 'text/html; charset=utf-8'], $body);
    }

    protected function getStatusCode(\Throwable $e)
    {
        return $e instanceof HttpException ? $e->getCode() : StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }

    /**
     * 忽略报告的异常.
     */
    protected function shouldntReport(\Throwable $e): bool
    {
        return !is_null(Arr::first($this->dontReport, fn($type) => $e instanceof $type));
    }

    /**
     * 运行环境是否是cli.
     */
    protected function runningInConsole(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
