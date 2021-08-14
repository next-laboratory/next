<?php

namespace Max;

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
}

$root = ('cli' === PHP_SAPI) ? '../' : './';

require __DIR__ . "/{$root}vendor/autoload.php";

(new App())->setRootPath($root)->start(function (App $app) {

    $http     = $app->http;
    $response = $http->response();

    $http->end($response);
});
