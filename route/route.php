<?php

/**
 * 路由定义文件
 */

use yao\data\Db;
use \yao\facade\Route;

Route::get('/', [\app\index\controller\Index::class, 'index']);

Route::get('/(\d+)?', function ($a = 2) {
    return view('index@index');
});