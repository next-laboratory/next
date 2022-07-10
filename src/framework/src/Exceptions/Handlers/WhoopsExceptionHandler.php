<?php

namespace Max\Framework\Exceptions\Handlers;

use Max\Http\Message\Response;
use Max\Http\Message\Stream\StringStream;
use Max\Http\Server\Contracts\ExceptionHandlerInterface;
use Max\Http\Server\Contracts\StoppableExceptionHandlerInterface;
use Max\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;
use Whoops\RunInterface;

class WhoopsExceptionHandler implements ExceptionHandlerInterface, StoppableExceptionHandlerInterface
{
    protected static array $preference = [
        'text/html'        => PrettyPageHandler::class,
        'application/json' => JsonResponseHandler::class,
        'application/xml'  => XmlResponseHandler::class,
    ];

    public function handle(Throwable $throwable, ServerRequestInterface $request): ?ResponseInterface
    {
        $whoops = new Run();
        [$handler, $contentType] = $this->negotiateHandler($request);

        $whoops->pushHandler($handler);
        $whoops->allowQuit(false);
        ob_start();
        $whoops->{RunInterface::EXCEPTION_HANDLER}($throwable);
        $content = ob_get_clean();

        return new Response(500, ['Content-Type' => $contentType], new StringStream($content));
    }

    public function isValid(Throwable $throwable): bool
    {
        return class_exists('Whoops\Run');
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

            $handler->addDataTableCallback('GET Data', [$request, 'getQueryParams']);
            $handler->addDataTableCallback('POST Data', [$request, 'getParsedBody']);
            $handler->addDataTableCallback('Server/Request Data', [$request, 'getServerParams']);
            $handler->addDataTableCallback('Cookies', [$request, 'getCookieParams']);
            $handler->addDataTableCallback('Files', [$request, 'getUploadedFiles']);
            $handler->addDataTableCallback('Attribute', [$request, 'getAttributes']);

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
