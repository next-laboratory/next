<?php

namespace Max;

require __DIR__ . '/../vendor/autoload.php';

(new App())->start(function (App $app) {

    $http     = $app->http;
    $response = $http->response();

    $http->end($response);

});
