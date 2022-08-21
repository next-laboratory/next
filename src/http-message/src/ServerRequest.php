<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message;

use Max\Http\Message\Bag\CookieBag;
use Max\Http\Message\Bag\FileBag;
use Max\Http\Message\Bag\ParameterBag;
use Max\Http\Message\Bag\ServerBag;
use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Stream\StringStream;
use Max\Utils\Arr;
use Max\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

use function strpos;

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
        UriInterface|string $uri,
        array $headers = [],
        StreamInterface|string|null $body = null,
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
    public static function createFromSwooleRequest($request, array $attributes = []): ServerRequest
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
        } elseif (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } elseif (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } elseif (isset($header['host'])) {
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

        $psrRequest                = new static($request->getMethod(), $uri, $header);
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
     * {@inheritDoc}
     */
    public function getServerParams()
    {
        return $this->serverParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withServerParams(array $serverParams)
    {
        $new               = clone $this;
        $new->serverParams = new ServerBag($serverParams);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookieParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies)
    {
        $new               = clone $this;
        $new->cookieParams = new CookieBag($cookies);
        return $new;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query)
    {
        $new              = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritDoc}
     * @return UploadedFile[]
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new                = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data)
    {
        $new             = clone $this;
        $new->parsedBody = $data instanceof ParameterBag ? $data : new ParameterBag((array) $data);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value)
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->set($name, $value);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name)
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->remove($name);

        return $new;
    }

    /**
     * 是否是ajax请求
     */
    public function isAjax(): bool
    {
        return strcasecmp('XMLHttpRequest', $this->getHeaderLine(HeaderInterface::HEADER_X_REQUESTED_WITH)) === 0;
    }

    /**
     * 判断path是否匹配.
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
     * 获取完整url.
     */
    public function fullUrl(): string
    {
        return $this->getUri()->__toString();
    }

    /**
     * 返回url.
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
     * 获取file.
     */
    public function file(string $field): ?UploadedFile
    {
        return Arr::get($this->getUploadedFiles(), $field);
    }

    /**
     * @return UploadedFile[]
     */
    public function files(): array
    {
        return $this->getUploadedFiles();
    }

    /**
     * Example: $request->isMethod('GET').
     */
    public function isMethod(string $method): bool
    {
        return strcasecmp($this->getMethod(), $method) === 0;
    }
}
