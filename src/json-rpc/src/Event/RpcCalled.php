<?php

namespace Max\JsonRpc\Event;

class RpcCalled
{
    public function __construct(
        protected string $method,
        protected array $params,
        protected mixed $id = null,
        protected string $jsonrpc = '2.0'
    ) {
    }
}
