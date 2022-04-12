<?php

namespace Max\Swagger\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Get extends Api
{
    protected string $method = 'get';
}
