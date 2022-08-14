<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message;

use Max\Http\Message\Bag\HeaderBag;
use Max\Http\Message\Stream\StringStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';

    protected HeaderBag $headers;

    protected ?StreamInterface $body = null;

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $new = clone $this;
        return $new->setProtocolVersion($version);
    }

    public function setProtocolVersion($version): static
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name)
    {
        return $this->headers?->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name)
    {
        return $this->headers?->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name)
    {
        if ($this->hasHeader($name)) {
            return implode(', ', $this->getHeader($name));
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value)
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        return $new->setHeader($name, $value);
    }

    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value)
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        return $new->setAddedHeader($name, $value);
    }

    public function setAddedHeader($name, $value): static
    {
        $this->headers->add($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name)
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        $new->headers->remove($name);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        return $new->setBody($body);
    }

    public function setBody(StreamInterface $body): static
    {
        $this->body = $body;
        return $this;
    }

    protected function formatBody(string|StreamInterface|null $body)
    {
        $this->body = $body instanceof StreamInterface ? $body : new StringStream((string) $body);
    }
}
