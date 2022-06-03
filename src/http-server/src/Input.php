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

class Input
{
    public function __construct(protected ServerRequestInterface $request)
    {
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->request->getQueryParams(), $key, $default);
    }

    public function post(string $key, $default = null)
    {
        return Arr::get($this->request->getParsedBody(), $key, $default);
    }

    public function all(): array
    {
        return $this->request->getParsedBody() + $this->request->getParsedBody();
    }
}
