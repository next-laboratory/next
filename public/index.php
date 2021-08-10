<?php

namespace Max;

ini_set('display_errors', 'On');
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;

$response = $http->response();

$http->end($response);
