<?php

/**
 * 路由定义文件
 */

use \Max\Facade\Route;

Route::get('/', 'App\Http\Controllers\Index@index')->alias('home')->cors('*');

Route::get('/request', [\App\Http\Controllers\Index::class, 'request'])->middleware(\App\Http\Middleware\BasicAuthentication::class);

Route::redirect('test(\d+)-(\w+)', 'https://www.chengyao.xyz/$1$2');