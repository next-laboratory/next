<?php

namespace app\index\facade;

use yao\Facade;

/**
 * @method static Validate data(array $data)
 * Class UserCheck
 * @package app\index\facade
 */
class UserCheck extends Facade
{

    protected static function getFacadeClass()
    {
        return \app\index\validate\UserCheck::class;
    }

}