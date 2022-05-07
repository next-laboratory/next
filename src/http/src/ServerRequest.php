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

namespace Max\Http;

use Max\Context\Context;
use Max\Http\Message\UploadedFile;
use Psr\{Http\Message\ServerRequestInterface, Http\Message\UriInterface};
use RuntimeException;

class ServerRequest implements ServerRequestInterface
{
    use Message;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getPsr7()->getMethod();
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        return $this->getPsr7()->getRequestTarget();
    }

    /**
     * @param mixed $requestTarget
     *
     * @return ServerRequestInterface
     */
    public function withRequestTarget($requestTarget)
    {
        return $this->getPsr7()->withRequestTarget($requestTarget);
    }

    /**
     * @param string $method
     *
     * @return ServerRequestInterface
     */
    public function withMethod($method)
    {
        return $this->getPsr7()->withMethod($method);
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->getPsr7()->getUri();
    }

    /**
     * @param UriInterface $uri
     * @param false        $preserveHost
     *
     * @return ServerRequestInterface
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        return $this->getPsr7()->withUri($uri, $preserveHost);
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->getPsr7()->getServerParams();
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams()
    {
        return $this->getPsr7()->getCookieParams();
    }

    /**
     * @param array $cookies
     *
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies)
    {
        return $this->getPsr7()->withCookieParams($cookies);
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->getPsr7()->getQueryParams();
    }

    /**
     * @param array $query
     *
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query)
    {
        return $this->getPsr7()->withQueryParams($query);
    }

    /**
     * @return UploadedFile[]
     */
    public function getUploadedFiles()
    {
        return $this->getPsr7()->getUploadedFiles();
    }

    /**
     * @param array $uploadedFiles
     *
     * @return ServerRequestInterface
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->getPsr7()->withUploadedFiles($uploadedFiles);
    }

    /**
     * @return array
     */
    public function getParsedBody()
    {
        return $this->getPsr7()->getParsedBody();
    }

    /**
     * @param array|object|null $data
     *
     * @return ServerRequestInterface
     */
    public function withParsedBody($data)
    {
        return $this->getPsr7()->withParsedBody($data);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->getPsr7()->getAttributes();
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->getPsr7()->getAttribute($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return ServerRequestInterface
     */
    public function withAttribute($name, $value)
    {
        return $this->getPsr7()->withAttribute($name, $value);
    }

    /**
     * @param string $name
     *
     * @return ServerRequestInterface
     */
    public function withoutAttribute($name)
    {
        return $this->getPsr7()->withoutAttribute($name);
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getPsr7()
    {
        if ($serverRequest = Context::get(ServerRequestInterface::class)) {
            return $serverRequest;
        }
        throw new RuntimeException('There is no server request instance in the context', 500);
    }
}
