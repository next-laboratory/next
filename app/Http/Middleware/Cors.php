<?php

namespace App\Http\Middleware;

/**
 * Class Cors
 * @package App\Http\Middleware
 */
class Cors extends \Max\Http\Middleware\Cors
{

    /**
     * 全局跨域
     * @var bool
     */
    protected $global = false;

    /**
     * 允许跨的域
     * @var array
     */
    protected $allowOrigin = [

    ];

}