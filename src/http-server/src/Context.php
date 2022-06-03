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
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    use Input, Writer;

    public ServerRequestInterface $request;
    protected array               $container = [];

    /**
     * @param ServerRequestInterface $request
     *
     * @return static
     */
    public static function create(ServerRequestInterface $request): static
    {
        $context          = new static();
        $context->request = $request;
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

    public function hasValue(string $key): bool
    {
        return Arr::has($this->container, $key);
    }

    public function removeValue(string $key = ''): void
    {
        empty($key) ? $this->container = [] : Arr::forget($this->container, $key);
    }
}
