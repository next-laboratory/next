<?php

namespace Max\Framework\Http\Middlewares;

use Max\Http\Message\Response;
use Max\Http\Message\Stream\StringStream;
use Max\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;

class WhoopsExceptionHandler implements MiddlewareInterface
{
    protected static array $preference = [
        'text/html'        => PrettyPageHandler::class,
        'application/json' => JsonResponseHandler::class,
        'application/xml'  => XmlResponseHandler::class,
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $throwable) {
            $whoops = new Run();
            [$handler, $contentType] = $this->negotiateHandler($request);

            $whoops->pushHandler($handler);
            $whoops->allowQuit(false);
            ob_start();
            $whoops->{Run::EXCEPTION_HANDLER}($throwable);
            $content = ob_get_clean();

            return new Response(500, ['Content-Type' => $contentType], new StringStream($content));
        }
    }

    protected function negotiateHandler(ServerRequestInterface $request)
    {
        $accepts = $request->getHeaderLine('accept');
        foreach (self::$preference as $contentType => $handler) {
            if (Str::contains($accepts, $contentType)) {
                return [$this->setupHandler(new $handler(), $request), $contentType];
            }
        }
        return [new PlainTextHandler(), 'text/plain'];
    }

    protected function setupHandler($handler, ServerRequestInterface $request)
    {
        if ($handler instanceof PrettyPageHandler) {
            $handler->handleUnconditionally(true);

            if (defined('BASE_PATH')) {
                $handler->setApplicationRootPath(BASE_PATH);
            }

            $handler->addDataTableCallback('PSR7 Query', [$request, 'getQueryParams']);
            $handler->addDataTableCallback('PSR7 Post', [$request, 'getParsedBody']);
            $handler->addDataTableCallback('PSR7 Server', [$request, 'getServerParams']);
            $handler->addDataTableCallback('PSR7 Cookie', [$request, 'getCookieParams']);
            $handler->addDataTableCallback('PSR7 File', [$request, 'getUploadedFiles']);
            $handler->addDataTableCallback('PSR7 Attribute', [$request, 'getAttributes']);

            try {
                $handler->addDataTableCallback('Session', [$request->session(), 'all']);
            } catch (\RuntimeException) {
            }

        } else if ($handler instanceof JsonResponseHandler) {
            $handler->addTraceToOutput(true);
        }

        return $handler;
    }
}
