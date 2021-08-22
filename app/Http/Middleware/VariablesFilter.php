<?php

namespace App\Http\Middleware;

use Max\Http\Middleware\Filter;

/**
 * 过滤全局数组，例如$_GET, $_POST, $_REQUEST
 * Class VariablesFilter
 * @package App\Http\Middleware
 */
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