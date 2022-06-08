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

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * @var string
     */
    protected string $path = '/';

    /**
     * @var string
     */
    protected string $scheme = 'http';

    /**
     * @var string
     */
    protected string $host = 'localhost';

    /**
     * @var int|string
     */
    protected int|string $port = 80;

    /**
     * @var string
     */
    protected string $query = '';

    /**
     * @var string
     */
    protected string $fragment = '';

    /**
     * @var string
     */
    protected string $authority = '';

    /**
     * @var string|mixed
     */
    protected string $userinfo = '';

    /**
     * 默认端口
     */
    protected const DEFAULT_PORT = [
        'https' => 443,
        'http'  => 80,
    ];

    /**
     * TODO
     *
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if ('' !== $uri) {
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
            if ('' !== $this->userinfo) {
                $port            = ($this->port > 655535 || $this->port < 0) ? '' : $this->getPortString();
                $this->authority = $this->userinfo . '@' . $this->host . $port;
            }
        }
    }

    /**
     * @return int|null
     */
    public function getDefaultPort(): ?int
    {
        return self::DEFAULT_PORT[$this->scheme] ?? null;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * @return string|void
     */
    public function getUserInfo()
    {
        return $this->userinfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int|string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        if ($scheme === $this->scheme) {
            return $this;
        }
        $new         = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $new           = clone $this;
        $new->userinfo = sprintf('%s%s', $user, $password ? (':' . $password) : '');
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        if ($host === $this->host) {
            return $this;
        }
        $new       = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        if ($port === $this->port) {
            return $this;
        }
        $new       = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        if ($path === $this->path) {
            return $this;
        }
        $new       = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        if ($query === $this->query) {
            return $this;
        }
        $new        = clone $this;
        $new->query = $query;
        return $new;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        if ($fragment === $this->fragment) {
            return $this;
        }
        $new           = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    /**
     * @inheritDoc
     */
    protected function getPortString()
    {
        if (('http' === $this->scheme && 80 === $this->port) ||
            ('https' === $this->scheme && 443 === $this->port)) {
            return '';
        }

        return ':' . $this->port;
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
            ('' === $this->query) ? '' : ('?' . $this->query),
            ('' === $this->fragment) ? '' : ('#' . $this->fragment),
        );
    }
}
