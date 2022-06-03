<?php

namespace Max\HttpServer;

use ArrayAccess;
use Max\Http\Message\Response;
use Max\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stringable;

class Context
{
    public ServerRequestInterface $request;
    public array                  $routeParams;
    protected array               $container = [];

    /**
     * @param ArrayAccess|array $data
     */
    public function JSON($data, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new Response($status, $headers, json_encode($data));
    }

    /**
     * @param Stringable|string $data
     */
    public function HTML($data, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        return new Response($status, $headers, (string)$data);
    }

    public function input(): Input
    {
        return new Input($this->request);
    }

    /**
     * @param Stringable|string $data
     */
    public function text($data, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response($status, $headers, (string)$data);
    }

    /**
     * Get a value from request context.
     */
    public function get(string $key)
    {
        return Arr::get($this->container, $key);
    }

    /**
     * Set a value to request context.
     */
    public function set(string $key, $value): void
    {
        Arr::set($this->container, $key, $value);
    }

    public function has(string $key): bool
    {
        return Arr::has($this->container, $key);
    }

    public function remove(string $key = ''): void
    {
        empty($key) ? $this->container = [] : Arr::forget($this->container, $key);
    }
}
