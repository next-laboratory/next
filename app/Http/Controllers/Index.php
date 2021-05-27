<?php

namespace App\Http\Controllers;

use App\Http\Controller;

class Index extends Controller
{
    public function index()
    {
        return view('index');
    }
}
