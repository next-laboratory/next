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
    /** @var array 允许域，全部可以使用`*` */
    protected array $allowOrigin = ['*'];

    /** @var array 附加的响应头 */
    protected array $addedHeaders = [
        HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS => 'true',
        HeaderInterface::HEADER_ACCESS_CONTROL_MAX_AGE           => 1800,
        HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_METHODS     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_HEADERS     => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldCrossOrigin($origin = $request->getHeaderLine(HeaderInterface::HEADER_ORIGIN))) {
            $headers                                                      = $this->addedHeaders;
            $headers[HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN] = $origin;
            if (strcasecmp($request->getMethod(), RequestMethodInterface::METHOD_OPTIONS) === 0) {
                return new Response(StatusCodeInterface::STATUS_NO_CONTENT, $headers);
            }
            $response = $handler->handle($request);
            foreach ($headers as $name => $header) {
                $response = $response->withHeader($name, $header);
            }
            return $response;
        }

        return $handler->handle($request);
    }

    /**
     * 允许跨域
     */
    protected function shouldCrossOrigin(string $origin)
    {
        if (empty($origin)) {
            return false;
        }
        return collect($this->allowOrigin)->first(function($allowOrigin) use ($origin) {
            return Str::is($allowOrigin, $origin);
        });
    }
}
