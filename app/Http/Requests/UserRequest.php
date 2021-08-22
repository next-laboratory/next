<?php

namespace App\Http\Requests;

use App\Http\Request;

class UserRequest extends Request
{
    protected $rule = [
        'id' => ['required' => true, 'max' => 3]
    ];

    protected $message = [
        'id' => ['required' => '请输入ID！'],
    ];
}