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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected string           $protocolVersion = '1.1';
    protected array            $headers         = [];
    protected ?StreamInterface $body            = null;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtoupper($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->headers[strtoupper($name)] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        if ($header = $this->getHeader($name)) {
            return implode(',', $header);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $this->headers[strtoupper($name)] = (array)$value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $this->headers[strtoupper($name)][] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        unset($this->headers[strtoupper($name)]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}
