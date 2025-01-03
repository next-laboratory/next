<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Next\JsonRpc\Message;

use InvalidArgumentException;
use JsonSerializable;
use Psr\Http\Message\ServerRequestInterface;

class Request implements JsonSerializable
{
    public function __construct(
        protected string $method,
        protected array $params = [],
        protected mixed $id = null,
        protected string $jsonrpc = '2.0',
    ) {
    }

    public static function createFromPsrRequest(ServerRequestInterface $request): static
    {
        if (!str_contains($request->getHeaderLine('Content-Type'), 'application/json')) {
            throw new InvalidArgumentException('Invalid Request', -32600);
        }
        $body  = $request->getBody()->getContents();
        $parts = json_decode($body, true);
        if (!isset($parts['jsonrpc'], $parts['method'])) {
            throw new InvalidArgumentException('Parse error', -32700);
        }
        return new static($parts['method'], $parts['params'] ?? [], $parts['id'] ?? null, $parts['jsonrpc']);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function hasId(): bool
    {
        return isset($this->id);
    }

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function setJsonrpc(string $jsonrpc): void
    {
        $this->jsonrpc = $jsonrpc;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
