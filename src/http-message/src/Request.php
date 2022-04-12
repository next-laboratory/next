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

use Max\Http\Message\Bags\HeaderBag;
use Max\Http\Message\Stream\StringStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @var UriInterface
     */
    protected UriInterface $uri;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $requestTarget = '/';

    /**
     * @param string $method
     * @param        $uri
     * @param array  $headers
     * @param null   $body
     * @param string $protocolVersion
     */
    public function __construct(string $method, $uri, array $headers = [], $body = null, string $protocolVersion = '1.1')
    {
        $this->method = $method;
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        $this->uri     = $uri;
        $this->headers = new HeaderBag($headers);
        if (!$body instanceof StreamInterface) {
            $body = new StringStream((string)$body);
        }
        $this->body            = $body;
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        if ('/' === $this->requestTarget) {
            return $this->uri->getPath() . $this->uri->getQuery();
        }
        return '/';
    }

    /**
     * @param $requestTarget
     *
     * @return Request
     */
    public function withRequestTarget($requestTarget)
    {
        $new                = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     *
     * @return Request
     */
    public function withMethod($method)
    {
        $new         = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param              $preserveHost
     *
     * @return Request
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        if (true === $preserveHost) {
            $uri = $uri->withHost($this->getHeaderLine('Host'));
        }
        $new->uri = $uri;

        return $new;
    }
}
