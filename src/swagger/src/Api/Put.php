<?php

namespace Max\Swagger\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Put extends Api
{
    protected string $method = 'put';
}
