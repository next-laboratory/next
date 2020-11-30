<?php


namespace app\index\hook;


class Patch
{
    public function hook(&$args)
    {
        $args = 155;
    }
}