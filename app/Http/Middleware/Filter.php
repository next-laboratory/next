<?php

namespace App\Http\Middleware;

class Filter extends \Max\Http\Middleware\Filter
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