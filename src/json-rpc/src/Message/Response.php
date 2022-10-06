<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc\Message;

use JsonSerializable;
use Max\Http\Message\Contract\HeaderInterface;
use Psr\Http\Message\ResponseInterface;

class Response implements JsonSerializable
{
    public function __construct(
        protected mixed $result,
        protected mixed $id,
        protected ?Error $error = null,
        protected string $jsonrpc = '2.0',
    ) {
    }

    public static function createFromPsrResponse(ResponseInterface $response)
    {
        if (! str_contains($response->getHeaderLine(HeaderInterface::HEADER_CONTENT_TYPE), 'application/json')) {
            throw new \Exception('Invalid Response', -32600);
        }
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getError(): ?Error
    {
        return $this->error;
    }

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function jsonSerialize()
    {
        return array_filter(get_object_vars($this));
    }
}
