<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;


Route::get('test', 'index@index/test')->alias('index@name');


Route::rule('/', [\App\Index\Controller\Index::class, 'index'])->alias('nae')->middleware(['index::class']);
Route::get('download', 'index@index/download');

Route::post('upload', 'index@index/upload')->alias('time');
