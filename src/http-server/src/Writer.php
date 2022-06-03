<?php

namespace Max\HttpServer;

use ArrayAccess;
use Max\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

trait Writer
{
    /**
     * @param ArrayAccess|array $data
     */
    public function JSON($data, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return $this->end(json_encode($data), $status, $headers);
    }

    /**
     * @param Stringable|string $data
     */
    public function HTML($data, int $status = 200, array $headers = []): ResponseInterface
    {
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        return $this->end((string)$data, $status, $headers);
    }

    public function end(null|StreamInterface|string $data = null, int $status = 200, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return new Response($status, $headers, $data, $protocolVersion);
    }
}
