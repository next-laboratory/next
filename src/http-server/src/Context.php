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

use Max\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    use Input, Writer;

    public ServerRequestInterface $request;
    public ResponseInterface      $response;
    protected array               $container = [];

    public static function create(ServerRequestInterface $request, ResponseInterface $response): static
    {
        $context           = new static();
        $context->request  = $request;
        $context->response = $response;
        return $context;
    }

    /**
     * Get a value from request context.
     */
    public function getValue(string $key)
    {
        return Arr::get($this->container, $key);
    }

    /**
     * Set a value to request context.
     */
    public function setValue(string $key, $value): void
    {
        Arr::set($this->container, $key, $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasValue(string $key): bool
    {
        return Arr::has($this->container, $key);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function removeValue(string $key = ''): void
    {
        empty($key) ? $this->container = [] : Arr::forget($this->container, $key);
    }
}
