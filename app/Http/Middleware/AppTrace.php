<?php

namespace App\Http\Middleware;

use Max\Http\Middleware\Trace;

class AppTrace extends Trace
{
    /**
     * Ajax请求是否显示Trace信息
     * @var bool
     */
    protected $ajaxAvailable = false;

}
