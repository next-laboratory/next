<?php

namespace App\Controllers;

use Max\Cache\Aspects\Cacheable;

class TestController extends \Exception
{
    #[Cacheable]
    public function index()
    {

    }
}
