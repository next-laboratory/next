<?php

namespace App\Http;

use Max\App;

/**
 * 自定义基础控制器
 * Class Controller
 * @package App\Http
 */
class Controller extends \Max\Http\Controller
{

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $app->request;
    }


}
