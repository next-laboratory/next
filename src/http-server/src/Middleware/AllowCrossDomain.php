<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server\Middleware;

use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Contract\RequestMethodInterface;
use Max\Http\Message\Contract\StatusCodeInterface;
use Max\Http\Message\Response;
use Max\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Max\Utils\collect;

class AllowCrossDomain implements MiddlewareInterface
{
    /**
     * @var array 允许域，全部可以使用`*`
     */
    protected array $allowOrigin = ['*'];

    /**
     * @var string[] 允许的头部
     */
    protected array $allowHeaders = [
        HeaderInterface::HEADER_AUTHORIZATION,
        HeaderInterface::HEADER_CONTENT_TYPE,
        'If-Match',
        'If-Modified-Since',
        'If-None-Match',
        'If-Unmodified-Since',
        'X-Csrf-Token',
        HeaderInterface::HEADER_X_REQUESTED_WITH,
    ];

    /**
     * @var array|string[] 允许的方法
     */
    protected array $allowMethods = [
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_POST,
        RequestMethodInterface::METHOD_PATCH,
        RequestMethodInterface::METHOD_PUT,
        RequestMethodInterface::METHOD_DELETE,
        RequestMethodInterface::METHOD_OPTIONS,
    ];

    /**
     * @var string 允许获取凭证
     */
    protected string $allowCredentials = 'true';

    /**
     * @var int Cookie存活时间
     */
    protected int $maxAge = 1800;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldCrossOrigin($origin = $request->getHeaderLine(HeaderInterface::HEADER_ORIGIN))) {
            $headers = $this->createCORSHeaders($origin);
            if (strcasecmp($request->getMethod(), RequestMethodInterface::METHOD_OPTIONS) === 0) {
                return new Response(StatusCodeInterface::STATUS_NO_CONTENT, $headers);
            }

            return $this->addHeadersToResponse($handler->handle($request), $headers);
        }

        return $handler->handle($request);
    }

    /**
     * 创建响应头部.
     */
    protected function createCORSHeaders(string $origin): array
    {
        return [
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS => $this->allowCredentials,
            HeaderInterface::HEADER_ACCESS_CONTROL_MAX_AGE           => $this->maxAge,
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_METHODS     => implode(', ', $this->allowMethods),
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_HEADERS     => implode(', ', $this->allowHeaders),
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN      => $origin,
        ];
    }

    /**
     * 将头部添加到响应.
     */
    protected function addHeadersToResponse(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }
        return $response;
    }

    /**
     * 允许跨域
     */
    protected function shouldCrossOrigin(string $origin)
    {
        if (empty($origin)) {
            return false;
        }
        return collect($this->allowOrigin)->first(function ($allowOrigin) use ($origin) {
            return Str::is($allowOrigin, $origin);
        });
    }
}
