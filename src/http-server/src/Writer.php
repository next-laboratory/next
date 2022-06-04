<?php

namespace Max\HttpServer;

use ArrayAccess;
use Max\HttpMessage\Cookie;
use Max\HttpMessage\Response;
use Max\HttpMessage\Stream\FileStream;
use Max\HttpMessage\Stream\StringStream;
use Max\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

trait Writer
{
    /**
     * @param string $name
     * @param string $value
     * @param int    $expires
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httponly
     * @param string $samesite
     *
     * @return $this
     */
    public function setCookie(
        string $name, string $value, int $expires = 3600, string $path = '/',
        string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = ''
    )
    {
        $cookie = new Cookie(...func_get_args());
        $this->response->withAddedHeader('Set-Cookie', $cookie->__toString());
        return $this;
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function contentType(string $contentType)
    {
        $this->response->withHeader('Content-Type', $contentType);
        return $this;
    }

    /**
     * @param ArrayAccess|array $data
     */
    public function JSON($data, int $status = 200): ResponseInterface
    {
        return $this->contentType('application/json; charset=utf-8')->end(json_encode($data), $status);
    }

    /**
     * @param Stringable|string $data
     */
    public function HTML($data, int $status = 200): ResponseInterface
    {
        return $this->contentType('text/html; charset=utf-8')->end((string)$data, $status);
    }

    /**
     * @param StreamInterface|string|null $data
     * @param int                         $status
     *
     * @return ResponseInterface
     */
    public function end(null|StreamInterface|string $data = null, int $status = 200): ResponseInterface
    {
        return $this->response
            ->withStatus($status)
            ->withBody($data instanceof StreamInterface ? $data : new StringStream((string)$data));
    }

    /**
     * 重定向
     *
     * @param string $url
     * @param int    $status
     *
     * @return ResponseInterface
     */
    public function redirect(string $url, int $status = 302): ResponseInterface
    {
        return $this->response->withHeader('Location', $url)->withStatus($status);
    }

    /**
     * 文件下载
     *
     * @param string $uri    文件路径
     * @param string $name   文件名
     * @param int    $offset 偏移量
     * @param int    $length 长度
     *
     * @return ResponseInterface
     */
    public function download(string $uri, string $name = '', int $offset = 0, int $length = -1): ResponseInterface
    {
        return $this->response
            ->withHeader('Content-Disposition', 'attachment;filename=' . $name ?: Str::random(10))
            ->withBody(new FileStream($uri, $offset, $length));
    }
}
