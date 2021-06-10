<?php

/**
 * 路由定义文件
 */

use \Max\Facade\Route;

Route::get('/', 'index@index')
    ->alias('home')
    ->cors('*')
    ;

Route::group(['ext' => '.html', 'middleware' => Login::class], function (\Max\Http\Route $route) {
    $route->get('index', function(){
        return view('index');
    })->cache(600);
    Route::get('home', [\App\Http\Controllers\Index::class, 'index']);
});