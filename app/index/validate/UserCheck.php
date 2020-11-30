<?php


namespace app\index\validate;

/**
 * Class UserCheck
 * @package app\index\validate
 */
class UserCheck extends \app\http\Validate
{

    protected bool $throwAble = true;

    protected array $rule = [
        'temporary-name' => ['max' => 10],
    ];

    protected array $notice = [
        'temporary-name' => ['max' => '用户名最长是十哦！']
    ];

    /**
     * 自定义验证规则示例
     * @param $field
     * @param $limit
     * @param $data
     * @param $regulation
     */
    protected function _checkMail($field, $limit, $data, $regulation)
    {
//        if ('true') {
//            return true;
//        } else {
//            $this->message[] = $this->notice[$field][$regulation] ?? 'false';
//            return false;
//        }
    }
}