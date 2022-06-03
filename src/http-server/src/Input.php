<?php

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

    public function post()
    {

    }

    public function all()
    {

    }
}
