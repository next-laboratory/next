<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message;

use Next\Http\Message\Bag\HeaderBag;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    protected UriInterface $uri;

    protected string $requestTarget = '/';

    public function __construct(
        protected string $method,
        string|UriInterface $uri,
        array $headers = [],
        string|null|StreamInterface $body = null,
        protected string $protocolVersion = '1.1'
    ) {
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->formatBody($body);
        $this->headers = new HeaderBag($headers);
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget === '/') {
            return $this->uri->getPath() . $this->uri->getQuery();
        }
        return '/';
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        if ($requestTarget === $this->requestTarget) {
            return $this;
        }
        $new                = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): RequestInterface
    {
        if ($method === $this->method) {
            return $this;
        }
        $new         = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }
        $new = clone $this;
        if ($preserveHost === true) {
            $uri = $uri->withHost($this->getHeaderLine('Host'));
        }
        $new->uri = $uri;

        return $new;
    }
}
