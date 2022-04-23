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

class Cookie
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
     */
    public function __construct(
        protected string $name,
        protected string $value,
        protected int    $expires = 0,
        protected string $path = '/',
        protected string $domain = '',
        protected bool   $secure = false,
        protected bool   $httponly = false,
        protected string $samesite = '',
    )
    {
        if ($this->samesite && !in_array(strtolower($samesite), ['lax', 'none', 'strict'])) {
            throw new InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @param int $expires
     */
    public function setExpires(int $expires): void
    {
        $this->expires = $expires;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    /**
     * @param bool $httponly
     */
    public function setHttponly(bool $httponly): void
    {
        $this->httponly = $httponly;
    }

    /**
     * @param string|null $samesite
     */
    public function setSamesite(?string $samesite): void
    {
        $this->samesite = $samesite;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = $this->name . '=';
        if ($this->value === '') {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; max-age=-31536001';
        } else {
            $str .= $this->value;
            if ($this->expires !== 0) {
                $str .= '; expires=' . gmdate('D, d-m-Y H:i:s T', $this->expires) . '; max-age=' . $this->getMaxAge();
            }
        }
        if ($this->path) {
            $str .= '; path=' . $this->path;
        }
        if ($this->domain) {
            $str .= '; domain=' . $this->domain;
        }
        if ($this->secure) {
            $str .= '; secure';
        }
        if ($this->httponly) {
            $str .= '; httponly';
        }
        if ($this->samesite) {
            $str .= '; samesite=' . $this->samesite;
        }
        return $str;
    }

    /**
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->expires !== 0 ? $this->expires - time() : 0;
    }

    /**
     * @param string $str
     *
     * @return Cookie
     */
    public static function parse(string $str): Cookie
    {
        $parts = [
            'expires'  => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,
            'httponly' => false,
            'samesite' => '',
        ];
        foreach (explode(';', $str) as $part) {
            if (!str_contains($part, '=')) {
                $key   = $part;
                $value = true;
            } else {
                [$key, $value] = explode('=', trim($part), 2);
                $value = trim($value);
            }
            switch ($key = trim(strtolower($key))) {
                case 'max-age':
                    $parts['expires'] = time() + (int)$value;
                    break;
                default:
                    if (array_key_exists($key, $parts)) {
                        $parts[$key] = $value;
                    } else {
                        $parts['name']  = $key;
                        $parts['value'] = $value;
                    }
            }
        }
        return new static(
            $parts['name'], $parts['value'],
            (int)$parts['expires'], $parts['path'],
            $parts['domain'], (bool)$parts['secure'],
            (bool)$parts['httponly'], $parts['samesite']
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
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
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function isHttponly(): bool
    {
        return $this->httponly;
    }

    /**
     * @return string|null
     */
    public function getSamesite(): ?string
    {
        return $this->samesite;
    }
}
