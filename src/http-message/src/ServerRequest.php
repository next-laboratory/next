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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
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
     * {@inheritDoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withServerParams(array $serverParams): ServerRequestInterface
    {
        $new               = clone $this;
        $new->serverParams = new ServerBag($serverParams);
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new               = clone $this;
        $new->cookieParams = new CookieBag($cookies);
        return $new;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new              = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritDoc}
     * @return UploadedFile[]
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new                = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody(): object|array|null
    {
        return $this->parsedBody->all();
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        $new             = clone $this;
        $new->parsedBody = $data instanceof ParameterBag ? $data : new ParameterBag((array)$data);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes(): array
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
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->set($name, $value);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
        $new             = clone $this;
        $new->attributes = clone $this->attributes;
        $new->attributes->remove($name);

        return $new;
    }
}
