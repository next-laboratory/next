<?php

/**
 * 路由定义文件
 */

use \Max\Facade\Route;

Route::get('/', 'index@index')
    ->alias('home')
    ->cache(600)
    ->middleware(\App\Http\Middleware\GlobalCross::class);

Route::group(['ext' => '.html', 'middleware' => \App\Http\Middleware\GlobalCross::class], function (\Max\Http\Route $route) {
    $route->get('index', function () {
        return view('index');
    })->cache(600)->cors('*');
    Route::get('home', [\App\Http\Controllers\Index::class, 'index']);
});
