<?php

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Http\Requests\UserRequest;

class Index extends Controller
{

    public function index()
    {
        return view('index');
    }

    public function request(UserRequest $request)
    {
        dump($request->get('id'));
    }
}
