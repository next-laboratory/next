<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Server;

use Max\Http\Message\Contract\HeaderInterface;
use Max\Http\Message\Cookie;
use Max\Http\Message\Response as PsrResponse;
use Max\Utils\Contract\Arrayable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stringable;

use function Max\Utils\data_to_xml;

class Response extends PsrResponse
{
    /**
     * Create a JSON response.
     *
     * @param array|Arrayable|string $data
     */
    public static function JSON($data, int $status = 200): ResponseInterface
    {
        if (! is_string($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return new static($status, ['Content-Type' => 'application/json; charset=utf-8'], $data);
    }

    /**
     * Create a JSONP response.
     *
     * @param array|Arrayable $data
     */
    public static function JSONP(ServerRequestInterface $request, $data, int $status = 200): ResponseInterface
    {
        if ($callback = $request->query('callback')) {
            if (! is_string($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            return new static($status, [HeaderInterface::HEADER_CONTENT_TYPE => 'application/javascript; charset=utf-8'], sprintf('%s(%s)', $callback, $data));
        }
        return static::JSON($data, $status);
    }

    /**
     * Create a HTML response.
     *
     * @param string|Stringable $data
     */
    public static function HTML($data, int $status = 200): ResponseInterface
    {
        return new static($status, [HeaderInterface::HEADER_CONTENT_TYPE => 'text/html; charset=utf-8'], (string) $data);
    }

    /**
     * Create a text response.
     */
    public static function text(string $content, int $status = 200): ResponseInterface
    {
        return new static($status, [HeaderInterface::HEADER_CONTENT_TYPE => 'text/plain; charset=utf-8'], $content);
    }

    /**
     * Create a XML response.
     */
    public static function XML(iterable $data, string $root = 'root', string $encoding = 'utf-8', int $status = 200): ResponseInterface
    {
        $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
        $xml .= '<' . $root . '>';
        $xml .= data_to_xml($data);
        $xml .= '</' . $root . '>';
        return new static($status, [HeaderInterface::HEADER_CONTENT_TYPE => 'application/xml; charset=utf-8'], $xml);
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $url, int $status = 302): ResponseInterface
    {
        return new static($status, ['Location' => $url]);
    }

    /**
     * Is the response empty?
     */
    public function isEmpty(): bool
    {
        return in_array($this->statusCode, [204, 304]);
    }

    /**
     * Set cookie.
     */
    public function withCookie(
        string $name,
        string $value,
        int $expires = 3600,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = false,
        string $sameSite = ''
    ): static {
        $cookie = new Cookie(...func_get_args());
        return $this->withAddedHeader('Set-Cookie', $cookie->__toString());
    }

    public function setCookie(
        string $name,
        string $value,
        int $expires = 3600,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httponly = false,
        string $sameSite = ''
    ) {
        $cookie = new Cookie(...func_get_args());
        return $this->setAddedHeader('Set-Cookie', $cookie->__toString());
    }
}
