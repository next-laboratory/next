<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message;

use Max\Http\Message\Bag\HeaderBag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
    protected const PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    protected string $reasonPhrase = '';

    public function __construct(
        protected int $statusCode = 200,
        array $headers = [],
        null|string|StreamInterface $body = null,
        protected string $protocolVersion = '1.1'
    ) {
        $this->reasonPhrase = static::PHRASES[$statusCode] ?? '';
        $this->formatBody($body);
        $this->headers = new HeaderBag($headers);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if ($code === $this->statusCode) {
            return $this;
        }
        $new = clone $this;
        return $new->setStatusCode($code, $reasonPhrase);
    }

    public function setStatusCode($code, $reasonPhrase = ''): static
    {
        $this->statusCode   = $code;
        $this->reasonPhrase = $reasonPhrase ?: (self::PHRASES[$code] ?? '');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase ?: (self::PHRASES[$this->getStatusCode()] ?? '');
    }

    /**
     * Is response invalid?
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function isInvalid(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     */
    public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     */
    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     */
    public function isOk(): bool
    {
        return $this->statusCode === 200;
    }

    /**
     * Is the response forbidden?
     */
    public function isForbidden(): bool
    {
        return $this->statusCode === 403;
    }

    /**
     * Is the response a not found error?
     */
    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * Is the response a redirect of some form?
     */
    public function isRedirect(string $location = null): bool
    {
        return in_array($this->statusCode, [201, 301, 302, 303, 307, 308])
            && ($location === null || $location == $this->getHeaderLine('Location'));
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
