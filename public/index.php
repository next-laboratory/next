<?php

namespace Max\Foundation;

require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;

$response = $http->response();

$http->end($response);
