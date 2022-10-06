<?php

namespace Max\JsonRpc\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RpcService
{
    public function __construct(
        public string $name
    ) {
    }
}
