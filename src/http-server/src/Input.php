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

namespace Max\HttpServer;

use InvalidArgumentException;
use Max\HttpMessage\UploadedFile;

trait Input
{
    public function header(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    public function session(string $key, $value = null)
    {
        if (is_null($value)) {
            return $this->request->session?->get($key);
        }
        return $this->request->session?->set($key, $value);
    }

    public function server(string $name)
    {
        return $this->request->getServerParams()[strtoupper($name)] ?? null;
    }

    public function isMethod(string $method): bool
    {
        return 0 === strcasecmp($this->request->getMethod(), $method);
    }

    public function url(): string
    {
        return $this->request->getUri()->__toString();
    }

    public function cookie(string $name): ?string
    {
        return $this->request->getCookieParams()[strtoupper($name)] ?? null;
    }

    public function isAjax(): bool
    {
        return 0 === strcasecmp('XMLHttpRequest', $this->request->getHeaderLine('X-REQUESTED-WITH'));
    }

    public function isPath(string $path): bool
    {
        $requestPath = $this->request->getUri()->getPath();

        return 0 === strcasecmp($requestPath, $path) || preg_match("#^{$path}$#iU", $requestPath);
    }

    public function raw(): string
    {
        return $this->request->getBody()->getContents();
    }

    public function get(null|array|string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->request->getQueryParams());
    }

    public function post(null|array|string $key = null, mixed $default = null): mixed
    {
        return $this->input($key, $default, $this->request->getParsedBody());
    }

    public function input(null|array|string $key = null, mixed $default = null, ?array $from = null): mixed
    {
        $from ??= $this->all();
        if (is_null($key)) {
            return $from ?? [];
        }
        if (is_array($key)) {
            $return = [];
            foreach ($key as $value) {
                $return[$value] = $this->isEmpty($from, $value) ? ($default[$value] ?? null) : $from[$value];
            }

            return $return;
        }
        return $this->isEmpty($from, $key) ? $default : $from[$key];
    }

    protected function isEmpty(array $haystack, $needle): bool
    {
        return !isset($haystack[$needle]) || '' === $haystack[$needle];
    }

    public function file(string $field): ?UploadedFile
    {
        return $this->request->getUploadedFiles()[$field] ?? null;
    }

    public function all(): array
    {
        return $this->request->getParsedBody() + $this->request->getParsedBody();
    }
}
