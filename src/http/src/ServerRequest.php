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

use InvalidArgumentException;
use Max\Http\Message\UploadedFile;
use Max\Routing\Route;
use Max\Utils\Context;
use Psr\{Http\Message\ServerRequestInterface, Http\Message\UriInterface};
use RuntimeException;
use Swoole\Http\Request;

class ServerRequest implements ServerRequestInterface
{
    use Message;

    /**
     * @param Route|null $route
     *
     * @return Route|void
     */
    public function route(?Route $route = null)
    {
        $key = Route::class;
        if (is_null($route)) {
            return Context::get($key);
        }
        Context::put($key, $route);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function header(string $name): string
    {
        return $this->getHeaderLine($name);
    }

    /**
     * @param string $name
     *
     * @return ?string
     */
    public function server(string $name): ?string
    {
        return $this->getServerParams()[strtoupper($name)] ?? null;
    }

    /**
     * 请求类型判断
     *
     * @param string $method 请求类型
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return 0 === strcasecmp($method, $this->getMethod());
    }

    /**
     * 获取请求的url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->getUri()->__toString();
    }

    /**
     * 单个cookie
     *
     * @param string $name
     *
     * @return ?string
     */
    public function cookie(string $name): ?string
    {
        return $this->getCookieParams()[strtoupper($name)] ?? null;
    }

    /**
     * 判断是否ajax请求
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return 0 === strcasecmp('XMLHttpRequest', $this->getHeaderLine('X-REQUESTED-WITH'));
    }

    /**
     * 判断请求的地址是否匹配当前请求的地址
     *
     * @param string $path
     *
     * @return bool
     */
    public function is(string $path): bool
    {
        $requestPath = $this->getUri()->getPath();

        return 0 === strcasecmp($requestPath, $path) || preg_match("#^{$path}$#iU", $requestPath);
    }

    /**
     * get请求参数
     *
     * @param array|string|null $key     请求的参数列表
     * @param mixed             $default 字符串参数的默认值
     *
     * @return mixed
     */
    public function get(null|array|string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->getQueryParams());
    }

    /**
     * 获取POST参数
     *
     * @param array|string|null $key     请求的参数列表
     * @param mixed             $default 字符串参数的默认值
     *
     * @return mixed
     */
    public function post(null|array|string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->getParsedBody());
    }

    /**
     * GET + POST
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getParsedBody() + $this->getQueryParams();
    }

    /**
     * 原始数据
     *
     * @return string
     */
    public function raw(): string
    {
        return $this->getPsr7()->getBody()->getContents();
    }

    /**
     * 判断请求的参数是不是空
     *
     * @param array $haystack
     * @param       $needle
     *
     * @return bool
     */
    protected function isEmpty(array $haystack, $needle): bool
    {
        return !isset($haystack[$needle]) || '' === $haystack[$needle];
    }

    /**
     * @param null       $key
     * @param null       $default
     * @param array|null $from
     *
     * @return mixed
     */
    public function input($key = null, mixed $default = null, ?array $from = null): mixed
    {
        $from ??= $this->all();

        if (is_null($key)) {
            return $from ?? [];
        }
        if (is_scalar($key)) {
            return $this->isEmpty($from, $key) ? $default : $from[$key];
        }
        if (is_array($key)) {
            $return = [];
            foreach ($key as $value) {
                $return[$value] = $this->isEmpty($from, $value) ? ($default[$value] ?? null) : $from[$value];
            }

            return $return;
        }
        throw new InvalidArgumentException('InvalidArgument！');
    }

    /**
     * @param string $field
     *
     * @return UploadedFile|null
     */
    public function file(string $field): ?UploadedFile
    {
        return $this->getUploadedFiles()[$field] ?? null;
    }

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

    /**
     * @param ServerRequestInterface $serverRequest
     */
    public function setPsr7(ServerRequestInterface $serverRequest)
    {
        Context::put(ServerRequestInterface::class, $serverRequest);
    }

    /**
     * @return mixed
     */
    public function getSwooleRequest()
    {
        return Context::get(Request::class);
    }
}
