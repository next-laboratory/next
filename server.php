<?php

namespace Max;

if (is_file($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
    return false;
}

require __DIR__ . "/vendor/autoload.php";

(new App())->setRootPath(('cli' === PHP_SAPI) ? '../' : './')->start(function (App $app) {

    $http     = $app->http;
    $response = $http->response();

    $http->end($response);
});
