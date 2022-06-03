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

namespace Max\HttpMessage;

use Max\HttpMessage\Bags\FileBag;
use Max\HttpMessage\Bags\InputBag;
use Max\HttpMessage\Bags\ParameterBag;
use Max\HttpMessage\Bags\ServerBag;
use Max\HttpMessage\Stream\StringStream;
use Psr\Http\Message\ServerRequestInterface;
use function strpos;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected array $serverParams;
    protected array $cookieParams;
    protected array $queryParams;
    protected array $attributes;
    protected array $uploadedFiles;
    protected array $parsedBody;

    /**
     * uri部分代码来自hyperf
     *
     * @param \Swoole\Http\Request $request
     *
     * @return static
     */
    public static function createFromSwooleRequest($request): ServerRequest
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

        $psrRequest                = new static($request->getMethod(), $uri, $header);
        $psrRequest->serverParams  = array_change_key_case($server, CASE_UPPER);
        $psrRequest->parsedBody    = $request->post ?? [];
        $psrRequest->body          = new StringStream($request->getContent());
        $psrRequest->cookieParams  = array_change_key_case($request->cookie ?? [], CASE_UPPER);
        $psrRequest->queryParams   = $request->get ?? [];
        $psrRequest->uploadedFiles = $request->files ?? [];

        return $psrRequest;
    }

    /**
     * @param \Workerman\Protocols\Http\Request $request
     *
     * @return static
     */
    public static function createFromWorkermanRequest($request)
    {
        $psrRequest                = new static(
            $request->method(),
            new Uri($request->uri()),
            $request->header(),
            $request->rawBody()
        );
        $psrRequest->queryParams   = $request->get() ?: [];
        $psrRequest->parsedBody    = $request->post() ?: [];
        $psrRequest->cookieParams  = $request->cookie() ?: [];
        $psrRequest->uploadedFiles = $request->file() ?: [];

        return $psrRequest;
    }

    /**
     * @return static
     */
    public static function createFromGlobals()
    {
        $psrRequest                = new static(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'],
            apache_request_headers(),
            file_get_contents('php://input')
        );
        $psrRequest->queryParams   = $_GET;
        $psrRequest->parsedBody    = $_POST;
        $psrRequest->uploadedFiles = $_FILES;
        $psrRequest->cookieParams  = $_COOKIE;

        return $psrRequest;
    }

    /**
     * @inheritDoc
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @inheritDoc
     */
    public function withServerParams(array $serverParams)
    {
        $this->serverParams = array_change_key_case($serverParams, CASE_UPPER);
        return $this;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookieParams = array_change_key_case($cookies, CASE_UPPER);
        return $this;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query)
    {
        $this->queryParams = $query;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data)
    {
        $this->parsedBody = (array)$data;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }
}
