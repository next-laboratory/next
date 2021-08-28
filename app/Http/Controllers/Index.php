<?php

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Http\Requests\UserRequest;
use Max\Facade\DB;

class Index extends Controller
{

    public function index()
    {
        DB::name('users')->select()->each(function ($item) {
            dump($item);
        });
        return view('index');
    }

    public function request(UserRequest $request)
    {
        dump($request->get('id'));
    }
}
