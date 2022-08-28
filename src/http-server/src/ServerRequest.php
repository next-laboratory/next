<?php

namespace Max\Http\Server;

use Max\Http\Message\Bag\CookieBag;
use Max\Http\Message\Bag\FileBag;
use Max\Http\Message\Bag\ParameterBag;
use Max\Http\Message\Bag\ServerBag;
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\ServerRequest as PsrServerRequest;
use Max\Http\Message\Stream\StringStream;
use Max\Http\Message\UploadedFile;
use Max\Http\Message\Uri;
use Max\Utils\Arr;
use Max\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends PsrServerRequest
{
    /**
     * uri部分代码来自hyperf.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return static
     */
    public static function createFromSwooleRequest($request, array $attributes = []): \Max\Http\Message\ServerRequest
    {
        $server  = $request->server;
        $header  = $request->header;
        $uri     = (new Uri())->withScheme(isset($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');
        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri             = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri     = $uri->withPort($hostHeaderParts[1]);
            }
        } else if (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } else if (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } else if (isset($header['host'])) {
            $hasPort = true;
            if (strpos($header['host'], ':')) {
                [$host, $port] = explode(':', $header['host'], 2);
                if ($port != $uri->getDefaultPort()) {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }

            $uri = $uri->withHost($host);
        }

        if (!$hasPort && isset($server['server_port'])) {
            $uri = $uri->withPort($server['server_port']);
        }

        $hasQuery = false;
        if (isset($server['request_uri'])) {
            $requestUriParts = explode('?', $server['request_uri']);
            $uri             = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri      = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        $protocol                  = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';
        $psrRequest                = new static($request->getMethod(), $uri, $header, $protocol);
        $psrRequest->serverParams  = new ServerBag($server);
        $psrRequest->parsedBody    = new ParameterBag($request->post ?? []);
        $psrRequest->body          = new StringStream($request->getContent());
        $psrRequest->cookieParams  = new CookieBag($request->cookie ?? []);
        $psrRequest->queryParams   = new ParameterBag($request->get ?? []);
        $psrRequest->uploadedFiles = new FileBag($request->files ?? []); // TODO Convert to UploadedFiles.
        $psrRequest->attributes    = new ParameterBag($attributes);

        return $psrRequest;
    }

    /**
     * @param \Workerman\Protocols\Http\Request $request
     */
    public static function createFromWorkerManRequest($request, array $attributes = []): static
    {
        $psrRequest                = new static(
            $request->method(), new Uri($request->uri()),
            $request->header(), $request->rawBody()
        );
        $psrRequest->queryParams   = new ParameterBag($request->get() ?: []);
        $psrRequest->parsedBody    = new ParameterBag($request->post() ?: []);
        $psrRequest->cookieParams  = new CookieBag($request->cookie());
        $psrRequest->uploadedFiles = new FileBag($request->file());
        $psrRequest->attributes    = new ParameterBag($attributes);

        return $psrRequest;
    }

    public static function createFromGlobals(): static
    {
        $psrRequest                = new static(
            $_SERVER['REQUEST_METHOD'],
            new Uri($_SERVER['REQUEST_URI']),
            apache_request_headers(),
            file_get_contents('php://input')
        );
        $psrRequest->serverParams  = new ServerBag($_SERVER);
        $psrRequest->cookieParams  = new CookieBag($_COOKIE);
        $psrRequest->queryParams   = new ParameterBag($_GET);
        $psrRequest->parsedBody    = new ParameterBag($_POST);
        $psrRequest->uploadedFiles = FileBag::createFromGlobal();

        return $psrRequest;
    }

    /**
     * @param \Amp\Http\Server\Request $request
     */
    public static function createFromAmp($request): static
    {
        $uri                      = $request->getUri();
        $psrRequest               = new static($request->getMethod(), $uri, $request->getHeaders(), null);
        $psrRequest->cookieParams = new CookieBag();
        parse_str($uri->getQuery(), $query);
        $psrRequest->queryParams = new ParameterBag($query);
        foreach ($request->getCookies() as $requestCookie) {
            $psrRequest->cookieParams->set($requestCookie->getName(), $requestCookie->getValue());
        }
        return $psrRequest;
    }

    public static function createFromPsrRequest(ServerRequestInterface $request): static
    {
        $psrRequest                = new static($request->getMethod(), $request->getUri(), $request->getHeaders(), $request->getBody());
        $psrRequest->serverParams  = new ServerBag($request->getServerParams() ?: []);
        $psrRequest->cookieParams  = new CookieBag($request->getCookieParams() ?: []);
        $psrRequest->queryParams   = new ParameterBag($request->getQueryParams() ?: []);
        $psrRequest->parsedBody    = new ParameterBag($request->getParsedBody() ?: []);
        $psrRequest->uploadedFiles = new FileBag($request->getUploadedFiles() ?: []);

        return $psrRequest;
    }

    /**
     * Check whether it is an Ajax request.
     */
    public function isAjax(): bool
    {
        return strcasecmp('XMLHttpRequest', $this->getHeaderLine(HeaderInterface::HEADER_X_REQUESTED_WITH)) === 0;
    }

    /**
     * Check whether the request path matches the given pattern.
     */
    public function is(string $pattern): bool
    {
        if (($path = $this->getUri()->getPath()) !== '/') {
            $path = trim($path, '/');
        }
        $pattern = $pattern === '/' ? $pattern : trim($pattern, '/');
        return Str::is($pattern, $path);
    }

    /**
     * Example: $request->getCookie('session_id').
     */
    public function getCookie(string $name)
    {
        return $this->getCookieParams()[strtoupper($name)] ?? null;
    }

    /**
     * Get a server variable.
     */
    public function getServer(string $name): ?string
    {
        return $this->serverParams->get($name);
    }

    /**
     * Get the full request url.
     */
    public function fullUrl(): string
    {
        return $this->getUri()->__toString();
    }

    /**
     * Get the request url.
     * Example: /users?id=1
     */
    public function url(): string
    {
        $uri = $this->getUri();
        $url = $uri->getPath();
        if (!empty($query = $uri->getQuery())) {
            $url .= '?' . $query;
        }
        return $url;
    }

    /**
     * Get an uploaded file.
     */
    public function file(string $field): ?UploadedFile
    {
        return Arr::get($this->getUploadedFiles(), $field);
    }

    /**
     * Check whether the requested method is the same as the entered one.
     * Example: $request->isMethod('GET').
     */
    public function isMethod(string $method): bool
    {
        return strcasecmp($this->getMethod(), $method) === 0;
    }
}
