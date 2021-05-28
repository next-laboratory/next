<?php

use Max\Facade\Route;

Route::get('index', function (\Max\Http\Response $response) {
    return $response->body('<h1>MaxPHP</h1>');
});
