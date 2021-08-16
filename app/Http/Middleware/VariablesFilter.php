<?php

namespace App\Http\Middleware;

use Max\Http\Middleware\Filter;

class VariablesFilter extends Filter
{

    /**
     * 过滤函数
     * @var string[]
     */
    protected $filters = [
        'trim',
        'htmlspecialchars|3',
    ];

    /**
     * 要过滤的全局数组
     * @var array
     */
    protected $vars = [
        'GET',
        'POST',
        'REQUEST',
    ];


}