<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message;

use Next\Http\Message\Bag\CookieBag;
use Next\Http\Message\Bag\FileBag;
use Next\Http\Message\Bag\ParameterBag;
use Next\Http\Message\Bag\ServerBag;
use Next\Http\Message\Stream\StandardStream;
use Next\Utils\Arr;
use Next\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected ServerBag $serverParams;

    protected CookieBag $cookieParams;

    protected ParameterBag $queryParams;

    protected ParameterBag $attributes;

    protected FileBag $uploadedFiles;

    protected ParameterBag $parsedBody;

    public function __construct(
        string $method,
        string|UriInterface $uri,
        array $headers = [],
        null|StreamInterface|string $body = null,
        string $protocolVersion = '1.1'
    ) {
        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
        $this->attributes    = new ParameterBag();
        $this->queryParams   = new ParameterBag();
        $this->uploadedFiles = new FileBag();
        $this->parsedBody    = new ParameterBag();
        $this->serverParams  = new ServerBag();
    }

    /**
     * uri部分代码来自hyperf.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return static
     */
    public static function createFromSwooleRequest($request, array $attributes = []): ServerRequestInterface
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
                $uri     = $uri->withPort((int) $hostHeaderParts[1]);
            }
        } elseif (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } elseif (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } elseif (isset($header['host'])) {
            $hasPort = true;
            if (strpos($header['host'], ':')) {
                $hostParts = explode(':', $header['host'], 2);
                $host      = $hostParts[0];
                if (isset($hostParts[1]) && (int) $hostParts[1] !== $uri->getDefaultPort()) {
                    $uri = $uri->withPort((int) $hostParts[1]);
                }
            } else {
                $host = $header['host'];
            }

            $uri = $uri->withHost($host);
        }

        if (! $hasPort && isset($server['server_port'])) {
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

        if (! $hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        $protocol                  = isset($server['server_protocol']) ? str_replace('HTTP/', '', $server['server_protocol']) : '1.1';
        $psrRequest                = new static($request->getMethod(), $uri, $header, $protocol);
        $psrRequest->serverParams  = new ServerBag($server);
        $psrRequest->parsedBody    = new ParameterBag($request->post ?? []);
        $psrRequest->body          = StandardStream::create((string) $request->getContent());
        $psrRequest->cookieParams  = new CookieBag($request->cookie ?? []);
        $psrRequest->queryParams   = new ParameterBag($request->get ?? []);
        $psrRequest->uploadedFiles = FileBag::loadFromFiles($request->files ?? []);
        $psrRequest->attributes    = new ParameterBag($attributes);

        return $psrRequest;
    }

    /**
     * @param \Workerman\Protocols\Http\Request $request
     */
    public static function createFromWorkerManRequest($request, array $attributes = []): ServerRequestInterface
    {
        $psrRequest                = new static(
            $request->method(), new Uri($request->host() . '/' . trim($request->uri(), '/')),
            $request->header(), $request->rawBody()
        );
        $psrRequest->queryParams   = new ParameterBag($request->get() ?: []);
        $psrRequest->parsedBody    = new ParameterBag($request->post() ?: []);
        $psrRequest->cookieParams  = new CookieBag($request->cookie());
        $psrRequest->uploadedFiles = FileBag::loadFromFiles($request->file() ?? []);
        $psrRequest->attributes    = new ParameterBag($attributes);
        $psrRequest->serverParams  = new ServerBag($_SERVER);

        return $psrRequest;
    }

    public static function createFromGlobals(): ServerRequestInterface
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
        $psrRequest->uploadedFiles = FileBag::loadFromFiles($_FILES);

        return $psrRequest;
    }

    public static function createFromPsrRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $psrRequest                = new static($request->getMethod(), $request->getUri(), $request->getHeaders(), $request->getBody());
        $psrRequest->serverParams  = new ServerBag($request->getServerParams() ?: []);
        $psrRequest->cookieParams  = new CookieBag($request->getCookieParams() ?: []);
        $psrRequest->queryParams   = new ParameterBag($request->getQueryParams() ?: []);
        $psrRequest->parsedBody    = new ParameterBag($request->getParsedBody() ?: []);
        $psrRequest->uploadedFiles = new FileBag($request->getUploadedFiles() ?: []);

        return $psrRequest;
    }

    public function getServerParams(): array
    {
        return $this->serverParams->all();
    }

    public function withServerParams(array $serverParams): ServerRequestInterface
    {
        $new               = clone $this;
        $new->serverParams = new ServerBag($serverParams);
        return $new;
    }

    public function getCookieParams(): array
    {
        return $this->cookieParams->all();
    }

    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new               = clone $this;
        $new->cookieParams = new CookieBag($cookies);
        return $new;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams->all();
    }

    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new              = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * @return UploadedFile[]
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles->all();
    }

    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new                = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    public function getParsedBody(): null|array|object
    {
        return $this->parsedBody->all();
    }

    public function withParsedBody($data): ServerRequestInterface
    {
        $new             = clone $this;
        $new->parsedBody = $data instanceof ParameterBag ? $data : new ParameterBag((array) $data);

        return $new;
    }

    public function getAttributes(): array
    {
        return $this->attributes->all();
    }

    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    public function withAttribute($name, $value): ServerRequestInterface
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->set($name, $value);

        return $new;
    }

    public function withoutAttribute($name): ServerRequestInterface
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->remove($name);

        return $new;
    }

    /**
     * 从queryParams中获取输入参数.
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->getQueryParams());
    }

    /**
     * 从parsedBody中获取输入参数.
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->getParsedBody());
    }

    /**
     * 获取输入参数，包括queryParams和parsedBody.
     */
    public function input(?string $key = null, mixed $default = null, ?array $input = null): mixed
    {
        $input ??= $this->all();
        return is_null($key) ? $input : ($input[$key] ?? $default);
    }

    /**
     * 全部输入参数.
     */
    public function all(): array
    {
        return $this->getQueryParams() + $this->getParsedBody();
    }

    /**
     * 验证输入参数是否有对应的键.
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    /**
     * 验证输入参数是否不为空.
     */
    public function has(string $key): bool
    {
        return ! empty($this->input($key));
    }

    /**
     * Check whether it is an Ajax request.
     */
    public function isAjax(): bool
    {
        return strcasecmp('XMLHttpRequest', $this->getHeaderLine('X-Requested-With')) === 0;
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
     * Example: /users?id=1.
     */
    public function url(): string
    {
        $uri = $this->getUri();
        $url = $uri->getPath();
        if (! empty($query = $uri->getQuery())) {
            $url .= '?' . $query;
        }
        return $url;
    }

    /**
     * Get an uploaded file.
     *
     * @return null|array<mixed, UploadedFileInterface>|UploadedFileInterface
     */
    public function file(string $field): null|array|UploadedFileInterface
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
