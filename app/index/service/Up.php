<?php

namespace app\index\service;

class Up
{
    public function boot()
    {
        if (1 != 2) {
            exit('非法请求');
        }
    }
}