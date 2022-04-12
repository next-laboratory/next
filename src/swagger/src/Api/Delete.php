<?php

namespace Max\Swagger\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Delete extends Api
{
    protected string $method = 'delete';
}
