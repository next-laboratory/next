<?php

namespace App\Http;

use Max\Http;

class Kernel extends Http
{

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [
//        \App\Http\Middleware\Debug::class,
//        \App\Http\Middleware\BasicAuth::class,
//        \App\Http\Middleware\GlobalCross::class,
    ];

    /**
     * 服务提供者
     * @var string[]
     */
    protected $providers = [
        \Max\DatabaseService::class,
        \Max\CacheService::class,
        \Max\ViewService::class,
        \Max\ValidatorService::class,
    ];

}