<?php

namespace App\Index\Controller;

use yao\facade\Request;

class Index
{
    public function index()
    {
        if (Request::isMethod('get')) {
            return view('index@index');
        }
        dump(Request::get());
    }

    public function list()
    {
        echo '111';
//        $name = new \yao\http\Request();
//        $ref = new ReflectionObject($name);
//        dump($ref);
    }
}
