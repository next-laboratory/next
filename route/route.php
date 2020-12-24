<?php

/**
 * 路由定义文件
 */

use \Yao\Facade\Route;

Route::rule('/', [\App\Index\Controller\Index::class, 'index']);
