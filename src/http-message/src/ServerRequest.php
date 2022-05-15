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

namespace Max\Http\Message;

use Max\Http\Message\Bags\FileBag;
use Max\Http\Message\Bags\InputBag;
use Max\Http\Message\Bags\ParameterBag;
use Max\Http\Message\Bags\ServerBag;
use Max\Http\Message\Stream\StringStream;
use Psr\Http\Message\ServerRequestInterface;
use function strpos;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var ServerBag
     */
    protected ServerBag $serverParams;

    /**
     * @var InputBag
     */
    protected InputBag $cookieParams;

    /**
     * @var InputBag
     */
    protected InputBag $queryParams;

    /**
     * @var ParameterBag
     */
    protected ParameterBag $attributes;

    /**
     * @var FileBag
     */
    protected FileBag $uploadedFiles;

    /**
     * @var InputBag
     */
    protected InputBag $parsedBody;

    /**
     * uri部分代码来自hyperf
     *
     * @param \Swoole\Http\Request $request
     *
     * @return static
     */
    public static function createFromSwooleRequest($request): ServerRequest
    {
        $server = $request->server;
        $header = $request->header;
        $uri = (new Uri())->withScheme(isset($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');
        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
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
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        $psrRequest = new static($request->getMethod(), $uri, $header);
        $psrRequest->serverParams = new ServerBag(array_change_key_case($server, CASE_UPPER));
        $psrRequest->parsedBody = new InputBag($request->post ?? []);
        $psrRequest->body = new StringStream($request->getContent());
        $psrRequest->cookieParams = new InputBag(array_change_key_case($request->cookie ?? [], CASE_UPPER));
        $psrRequest->queryParams = new InputBag($request->get ?? []);
        $psrRequest->uploadedFiles = new FileBag($request->files ?? []);
        $psrRequest->attributes = new ParameterBag();

        return $psrRequest;
    }

    /**
     * @param \Workerman\Protocols\Http\Request $request
     * @return static
     */
    public static function createFromWorkermanRequest($request)
    {
        $psrRequest = new static(
            $request->method(),
            new Uri($request->uri()),
            $request->header(),
            $request->rawBody()
        );
        $psrRequest->queryParams = new InputBag($request->get());
        $psrRequest->parsedBody = new InputBag($request->post());

        $psrRequest->cookieParams = new InputBag($request->cookie());
        $psrRequest->uploadedFiles = new FileBag($request->file());

        return $psrRequest;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams->all();
    }

    /**
     * @param array $serverParams
     *
     * @return ServerRequest
     */
    public function withServerParams(array $serverParams)
    {
        $new = clone $this;
        $new->serverParams = new ServerBag($serverParams);
        return $new;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams->all();
    }

    /**
     * @param array $cookies
     *
     * @return ServerRequest
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $newCookieParam = clone $this->cookieParams;
        $newCookieParam->add(array_change_key_case($cookies, CASE_UPPER));
        $new->cookieParams = $newCookieParam;

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
     * @param array $query
     *
     * @return ServerRequest
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $newQueryParams = clone $this->queryParams;
        $newQueryParams->add($query);
        $new->queryParams = $newQueryParams;

        return $new;
    }

    /**
     * @return array|FileBag
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles->all();
    }

    /**
     * @param array $uploadedFiles
     *
     * @return ServerRequest
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;
        $newUploadedFiles = clone $this->uploadedFiles;
        $newUploadedFiles->replace(array_merge($newUploadedFiles->all(), $uploadedFiles));
        $new->uploadedFiles = $newUploadedFiles;

        return $new;
    }

    /**
     * @return array
     */
    public function getParsedBody()
    {
        return $this->parsedBody->all();
    }

    /**
     * @param $data
     *
     * @return ServerRequest
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = new InputBag($data);

        return $new;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * @param $name
     * @param $default
     *
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * @param $name
     * @param $value
     *
     * @return ServerRequest
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $newAttribute = clone $this->attributes;
        $newAttribute->set($name, $value);
        $new->attributes = $newAttribute;

        return $new;
    }

    /**
     * @param $name
     *
     * @return ServerRequest
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;
        $newAttribute = clone $this->attributes;
        $newAttribute->remove($name);
        $new->attributes = $newAttribute;

        return $new;
    }
}
