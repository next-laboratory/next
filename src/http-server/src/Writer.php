<?php

namespace Max\HttpServer;

use ArrayAccess;
use Max\HttpMessage\Cookie;
use Max\HttpMessage\Stream\FileStream;
use Max\HttpMessage\Stream\StringStream;
use Max\Utils\Exceptions\FileNotFoundException;
use Max\Utils\Filesystem;
use Max\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

trait Writer
{
    /**
     * Set cookie.
     */
    public function setCookie(
        string $name, string $value, int $expires = 3600, string $path = '/',
        string $domain = '', bool $secure = false, bool $httponly = false, string $samesite = ''
    ): static
    {
        $cookie = new Cookie(...func_get_args());
        $this->response->withAddedHeader('Set-Cookie', $cookie->__toString());
        return $this;
    }

    /**
     * Generate a JSON response.
     *
     * @param ArrayAccess|array $data
     */
    public function JSON($data, int $status = 200): ResponseInterface
    {
        $this->response->withHeader('Content-Type', 'application/json; charset=utf-8');
        return $this->end(json_encode($data), $status);
    }

    /**
     * Generate a HTML response.
     *
     * @param Stringable|string $data
     */
    public function HTML($data, int $status = 200): ResponseInterface
    {
        $this->response->withHeader('Content-Type', 'charset=utf-8');
        return $this->end((string)$data, $status);
    }

    /**
     * Generate a response.
     */
    public function end(null|StreamInterface|string $data = null, int $status = 200): ResponseInterface
    {
        return $this->response
            ->withStatus($status)
            ->withBody($data instanceof StreamInterface ? $data : new StringStream((string)$data));
    }

    /**
     * Generate a redirect response.
     */
    public function redirect(string $url, int $status = 302): ResponseInterface
    {
        return $this->response->withHeader('Location', $url)->withStatus($status);
    }

    /**
     * Generate a file download response.
     *
     * @param string $uri    文件路径
     * @param string $name   文件名（留空则自动生成文件名）
     * @param int    $offset 偏移量
     * @param int    $length 长度
     *
     * @throws FileNotFoundException
     */
    public function download(string $uri, string $name = '', int $offset = 0, int $length = -1): ResponseInterface
    {
        if (!file_exists($uri)) {
            throw new FileNotFoundException('File does not exist.');
        }
        if (empty($name)) {
            $extension = Filesystem::extension($uri);
            if (!empty($extension)) {
                $extension = '.' . $extension;
            }
            $name = Str::random(10) . $extension;
        }
        return $this->response
            ->withHeader('Content-Disposition', 'attachment;filename=' . $name)
            ->withBody(new FileStream($uri, $offset, $length));
    }
}
