<?php

namespace app\http;

class Validate extends \yao\Validate
{
    /**
     * protected array $message = [];
     * 存放错误信息
     * protected array $data = [];
     * 存放需要验证的数据
     * protected array $rule = [];
     * 存放用户验证规则
     */

    /*
    * 用户自定义验证方法
    * User为验证规则名
    * @param $field
    * 验证时传入的用户数据键
    * @param $limit
    * 验证规则中对应的数据
    * @param $data
    * 用户需要验证的数据
    */
    protected function _checkUser($field, $limit, $data, $regulation)
    {

        //        if (true) {
        //            return true;
        //        } else {
        //            $this->message[] = $this->notice[$field][$regulation] ?? 'false';
        //            return false;
        //        }
        /**
         * 获取用户传递的数据使用$this->data[$field],或者使用$data
         * User可以自定义名称，前缀必须是_check
         * 验证成功返回true
         * 失败将错误信息添加到message属性返回false
         * $this->message[] = '';
         */
    }
}
