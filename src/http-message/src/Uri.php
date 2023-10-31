<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * 默认端口.
     */
    protected const DEFAULT_PORT = [
        'https' => 443,
        'http'  => 80,
    ];

    protected string $path = '/';

    protected string $scheme = 'http';

    protected string $host = 'localhost';

    protected int|string $port = 80;

    protected string $query = '';

    protected string $fragment = '';

    protected string $authority = '';

    /**
     * @var mixed|string
     */
    protected string $userinfo = '';

    /**
     * TODO.
     */
    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            if (false === $parts = parse_url($uri)) {
                throw new InvalidArgumentException("Unable to parse URI: {$uri}");
            }

            if (isset($parts['scheme'])) {
                $this->scheme = $parts['scheme'];
            }
            if (isset($parts['user'])) {
                $this->userinfo = isset($parts['pass']) ? sprintf('%s:%s', $parts['user'], $parts['pass']) : $parts['user'];
            }
            if (isset($parts['host'])) {
                $this->host = $parts['host'];
            }
            $this->port = $parts['port'] ?? $this->getDefaultPort();
            if (isset($parts['path'])) {
                $this->path = '/' . trim($parts['path'], '/');
            }
            if (isset($parts['query'])) {
                $this->query = $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $this->fragment = $parts['fragment'];
            }
            if ($this->userinfo !== '') {
                $port            = ($this->port > 655535 || $this->port < 0) ? '' : $this->getPortString();
                $this->authority = $this->userinfo . '@' . $this->host . $port;
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s://%s%s%s%s%s',
            $this->getScheme(),
            $this->getHost(),
            $this->getPortString(),
            $this->getPath(),
            ($this->query === '') ? '' : ('?' . $this->query),
            ($this->fragment === '') ? '' : ('#' . $this->fragment),
        );
    }

    public function getDefaultPort(): ?int
    {
        return self::DEFAULT_PORT[$this->scheme] ?? null;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        return $this->authority;
    }

    public function getUserInfo(): string
    {
        return $this->userinfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int|string|null
     */
    public function getPort(): int|string|null
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme): UriInterface
    {
        if ($scheme === $this->scheme) {
            return $this;
        }
        $new         = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $new           = clone $this;
        $new->userinfo = sprintf('%s%s', $user, $password ? (':' . $password) : '');
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withHost($host): UriInterface
    {
        if ($host === $this->host) {
            return $this;
        }
        $new       = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port): UriInterface
    {
        if ($port === $this->port) {
            return $this;
        }
        $new       = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path): UriInterface
    {
        if ($path === $this->path) {
            return $this;
        }
        $new       = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query):UriInterface
    {
        if ($query === $this->query) {
            return $this;
        }
        $new        = clone $this;
        $new->query = $query;
        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment):UriInterface
    {
        if ($fragment === $this->fragment) {
            return $this;
        }
        $new           = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    protected function getPortString(): string
    {
        if (($this->scheme === 'http' && $this->port === 80)
            || ($this->scheme === 'https' && $this->port === 443)) {
            return '';
        }

        return ':' . $this->port;
    }
}
