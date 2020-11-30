<?php

return [
    //是否开启调试
    'debug' => env('app.debug', false),
    //是否记录日志
    'log' => env('app.log', false),
    //自动开启session
    'auto_start' => env('app.auto_start', false),
    //默认时区
    'default_timezone' => env('app.default_timezone', 'PRC'),
    //参数过滤
    'filter' => ['trim', 'htmlspecialchars'],

    'exception_view' => ROOT . 'vendor/chengyao/framework/src/tpl/exception.html'
];
