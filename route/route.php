<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;

Route::rule('/', [\App\Index\Controller\Index::class, 'index']);
Route::get('todo', 'index@index/todo');
Route::get('download', 'index@index/download');

Route::post('upload', 'index@index/upload');