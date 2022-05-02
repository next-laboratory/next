<?php

namespace Max\Http\Annotations;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AutoController
{
    /**
     * @param string $prefix      前缀
     * @param array  $middlewares 中间件
     * @param array  $methods     请求方法
     */
    public function __construct(
        public string $prefix = '',
        public array  $middlewares = [],
        public array  $methods = ['GET', 'POST', 'HEAD']
    )
    {
    }
}
