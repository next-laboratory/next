<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message;

use Next\Http\Message\Bag\HeaderBag;
use Next\Http\Message\Stream\StandardStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';

    protected HeaderBag $headers;

    protected StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): MessageInterface
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

    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    public function hasHeader($name): bool
    {
        return $this->headers->has($name);
    }

    public function getHeader($name): array
    {
        return $this->headers->get($name);
    }

    public function getHeaderLine($name): string
    {
        if ($this->hasHeader($name)) {
            return implode(', ', $this->getHeader($name));
        }
        return '';
    }

    public function withHeader($name, $value): MessageInterface
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

    public function withAddedHeader($name, $value): MessageInterface
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        return $new->setAddedHeader($name, $value);
    }

    public function setAddedHeader($name, $value)
    {
        $this->headers->add($name, $value);
        return $this;
    }

    public function withoutHeader($name): MessageInterface
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        $new->headers->remove($name);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        return $new->setBody($body);
    }

    public function setBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }

    protected function formatBody(null|StreamInterface|string $body)
    {
        $this->body = StandardStream::create($body);
    }
}
