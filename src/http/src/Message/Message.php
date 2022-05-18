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
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected string $protocolVersion = '1.1';

    /**
     * @var HeaderBag
     */
    protected HeaderBag $headers;

    /**
     * @var StreamInterface|null
     */
    protected ?StreamInterface $body;

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $new                  = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * @inheritDoc
     * @return string[]
     */
    public function getHeader($name)
    {
        return $this->headers->get($name, []);
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        if ($header = $this->getHeader($name)) {
            return implode(',', $header);
        }

        return '';
    }

    /**
     * @param string          $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new          = clone $this;
        $newHeaders   = clone $this->headers;
        $new->headers = $newHeaders;

        $newHeaders->set($name, $value);

        return $new;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;

        $newHeaders = clone $this->headers;
        $newHeaders->addOne($name, $value);
        $new->headers = $newHeaders;

        return $new;
    }

    /**
     * @param $name
     *
     * @return static
     */
    public function withoutHeader($name)
    {
        $new        = clone $this;
        $newHeaders = clone $this->headers;
        if ($newHeaders->has($name)) {
            $newHeaders->remove($name);
        }
        $new->headers = $newHeaders;

        return $new;
    }

    /**
     * @return StreamInterface|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     *
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $new       = clone $this;
        $new->body = $body;
        return $new;
    }
}
