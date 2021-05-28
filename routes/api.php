<?php

use Max\Facade\Route;

Route::get('api', function () {
    return ['status' => 1, 'msg' => 'success'];
});