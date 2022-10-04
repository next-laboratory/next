<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validation;

use Max\Validation\Exception\ValidationException;

use function in_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_numeric;
use function mb_strlen;
use function preg_match;
use function strtolower;

trait Rules
{
    /**
     * @throws ValidationException
     */
    public function required($key, $value): void
    {
        if (empty($value)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '字段是必须的'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function max($key, $value, $max): void
    {
        if (!is_null($value)) {
            if (mb_strlen((string)$value, 'utf8') > (int)$max) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '的长度最大' . $max));
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function min($key, $value, $min): void
    {
        if (!is_null($value)) {
            if (mb_strlen((string)$value, 'utf8') < (int)$min) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '的长度最小' . $min));
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function length($key, $value, $min, $max): void
    {
        if (!is_null($value)) {
            $min = (int)$min;
            $max = (int)$max;
            if ($min > $max) {
                [$min, $max] = [$max, $min];
            }
            $length = mb_strlen((string)$value, 'utf8');
            if ($length < $min || $length > $max) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '的取值范围为' . $min . '-' . $max));
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function bool($key, $value): void
    {
        if (!is_null($value)) {
            if (!is_bool($value) || !in_array(strtolower((string)$value), ['on', 'yes', 'true', '1', 'off', 'no', 'false', '0'])) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '必须是布尔类型'));
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function in($key, $value, ...$in): void
    {
        if (!is_null($value) && !in_array($value, $in)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '必须在' . implode(',', $in) . '范围内'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function regex($key, $value, $regex): void
    {
        if (!is_null($value) && !preg_match($regex, $value, $match) && $match[0] == $value) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '的正则验证没有通过'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function confirm($key, $value, $confirm): void
    {
        if ($value != $this->getData($confirm)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '确认字段' . $confirm . '不一致'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function integer($key, $value): void
    {
        if (!is_null($value) && !is_int($value)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '必须由整数字符组成'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function numeric($key, $value): void
    {
        if (!is_null($value) && !is_numeric($value)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '必须是整数'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function array($key, $value): void
    {
        if (!is_null($value) && !is_array($value)) {
            $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '必须是数组'));
        }
    }

    /**
     * @throws ValidationException
     */
    public function isset($key, $value, ...$params): void
    {
        $this->array($key, $value);
        foreach ($params as $v) {
            if (!isset($value[$v])) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, '需要的字段' . $v . '不存在'));
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function email($key, $value): void
    {
        if (!is_null($value)) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $this->fail($this->getMessage($key . '.' . __FUNCTION__, $key . '的Email不合法'));
            }
        }
    }

    /**
     * 验证失败.
     *
     * @throws ValidationException
     */
    protected function fail(string $message): void
    {
        throw new ValidationException($message, 603);
    }
}
