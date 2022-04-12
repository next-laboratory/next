<?php

namespace Max\Swagger\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Patch extends Api
{
    protected string $method = 'patch';
}
