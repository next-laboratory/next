<?php

use function Max\env;

return [
    //自动开启session
    'auto_start' => env('app.auto_start', false),
    //参数过滤
    'filter'     => ['trim', 'htmlspecialchars'],
    //全局中间件
    'middleware' => [
//        \App\Http\Middleware\Debug::class,
//        \App\Http\Middleware\BasicAuth::class,
//        \App\Http\Middleware\GlobalCross::class,
    ],
    //服务提供者
    'provider'   => [
        \Max\DatabaseService::class,
        \Max\CacheService::class,
        \Max\ViewService::class,
        \Max\ValidatorService::class,
    ],
    'session'    => [
        'handler' => 'file',
        'path'    => env('storage_path') . 'session',
        'name'    => 'MAX'
    ],
    //响应Header中的Powered-By
    'powered_by' => 'MaxPHP'
];