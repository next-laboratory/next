<?php

/**
 * 用户自定义函数
 */

function alertRedirect($message)
{
    exit("<script>alert('$message');window.history.go(-1)</script>");
    //    exit("<script> alert('$message'); </script><meta http-equiv='Refresh' content='$time;URL=$url'>");
}


if (!function_exists('time_convert')) {
    /**
     * 时间个性化函数
     * @param string $value
     * 时间
     * @return false|string
     */
    function time_convert($time): string
    {
        if (!is_string($time)) {
            return '发布时间太远了！';
        }
        $diff = time() - strtotime($time);
        if ($diff < 60) {
            return $diff . '秒前';
        } elseif ($diff > 60 && $diff < 3600) {
            return round($diff / 60) . '分钟前';
        } elseif ($diff > 3600 && $diff < 86400) {
            return round($diff / 3600) . '小时前';
        } elseif ($diff > 86400 && $diff < 86400 * 5) {
            return round($diff / 86400) . '天前';
        }
        return date('Y/n/j', strtotime($time));
    }
}