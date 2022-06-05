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

use InvalidArgumentException;
use Max\HttpMessage\Stream\StringStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected string           $protocolVersion = '1.1';
    protected array            $headers         = [];
    protected array            $headersMap      = [];
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
        return isset($this->headersMap[strtoupper($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        $name = strtoupper($name);
        if (isset($this->headersMap[$name])) {
            return $this->headers[$this->headersMap[$name]];
        }
        return [];
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

    protected function normalizeHeaderName(string $name)
    {
        $key = strtoupper($name);
        if (!isset($this->headersMap[$key])) {
            $this->headersMap[$key] = $name;
        }
        return $this->headersMap[$key];
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $this->headers[$this->normalizeHeaderName($name)] = $this->formatValue($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $this->headers[$this->normalizeHeaderName($name)][] = $this->formatValue($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $key = strtoupper($name);
        if (isset($this->headersMap[$key])) {
            unset($this->headers[$this->headersMap[$key]]);
            unset($this->headersMap[$key]);
        }
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

    protected function formatBody(string|StreamInterface|null $body)
    {
        $this->body = $body instanceof StreamInterface ? $body : new StringStream((string)$body);
    }

    protected function formatHeaders(array $headers = [])
    {
        foreach ($headers as $key => $value) {
            $this->headersMap[strtoupper($key)] = $key;
            $this->headers[$key]                = $this->formatValue($value);
        }
    }

    /**
     * @param $value
     *
     * @return string[]
     */
    protected function formatValue($value): array
    {
        if (is_scalar($value)) {
            $value = [(string)$value];
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('The given header cannot be set.');
        }

        return array_values($value);
    }
}
