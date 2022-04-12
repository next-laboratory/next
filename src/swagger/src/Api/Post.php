<?php

namespace Max\Swagger\Api;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Post extends Api
{
    protected string $method = 'post';
}
