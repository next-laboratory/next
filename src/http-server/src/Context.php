<?php

namespace Max\HttpServer;

use ArrayAccess;
use Max\Http\Message\Response;
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
    public function JSON($data, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new Response($statusCode, $headers, json_encode($data));
    }

    /**
     * @param Stringable|string $data
     */
    public function HTML($data, int $statusCode = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        return new Response($statusCode, $headers, (string)$data);
    }

    /**
     * Get a value from request context.
     */
    public function get(string $key)
    {
        return $this->container[$key] ?? null;
    }

    /**
     * Set a value to request context.
     */
    public function set(string $key, $value): void
    {
        $this->container[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->container[$key]);
    }
}
