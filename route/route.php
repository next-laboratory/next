<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;


Route::get('test', 'index@index/test');


Route::rule('/', [\App\Index\Controller\Index::class, 'index'])->alias('dfds')->middleware(['index::class']);
Route::get('download', 'index@index/download');

Route::post('upload', 'index@index/upload')->cross([]);
