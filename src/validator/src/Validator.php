<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validator;

use Max\Validator\Bags\Errors;
use function explode;
use function is_array;

class Validator
{
    protected Rules $rules;

    protected array $data = [];

    protected array $message = [];

    protected array $valid = [];

    protected Errors $errors;

    protected bool $throwable = false;

    /**
     * @param  null|mixed        $key
     * @return null|array|string
     */
    public function getData($key = null)
    {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }

    /**
     * @param  mixed       $key
     * @param  mixed       $default
     * @return null|string
     */
    public function getMessage($key, $default = '验证失败')
    {
        return $this->message[$key] ?? $default;
    }

    public function isThrowable(): bool
    {
        return $this->throwable;
    }

    /**
     * @return Validator
     */
    public function setThrowable(bool $throwable): static
    {
        $this->throwable = $throwable;

        return $this;
    }

    /**
     * @return $this
     */
    public function make(array $data, array $rules, array $message = []): static
    {
        $this->rules   = new Rules($this);
        $this->errors  = new Errors();
        $this->data    = $data;
        $this->message = $message;

        foreach ($rules as $key => $rule) {
            $value = $this->getData($key);
            if (! is_array($rule)) {
                $rule = explode('|', $rule);
            }
            foreach ($rule as $ruleItem) {
                $ruleItem   = explode(':', $ruleItem, 2);
                $ruleName   = $ruleItem[0];
                $ruleParams = empty($ruleItem[1]) ? [] : explode(',', $ruleItem[1]);
                if ($this->rules->{$ruleName}($key, $value, ...$ruleParams)) {
                    $this->valid[$key] = $value;
                }
            }
        }

        return $this;
    }

    public function errors(): Errors
    {
        return $this->errors;
    }

    public function valid(): array
    {
        return $this->valid;
    }

    public function fails(): bool
    {
        return ! $this->errors->isEmpty();
    }

    public function failed(): array
    {
        return $this->errors->all();
    }
}
