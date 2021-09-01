<?php

namespace App\Http\Middleware;

use Max\Http\Middleware\Cors;

/**
 * Class Cors
 * @package App\Http\Middleware
 */
class AllowCrossDomain extends Cors
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