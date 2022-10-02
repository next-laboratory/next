<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validator;

use Max\Validator\Exception\ValidateException;
use function explode;
use function is_array;

class Validator
{
    use Rules;

    protected Error $error;
    protected bool  $validated = false;
    protected array $valid     = [];

    public function __construct(
        protected array $data,
        protected array $rules,
        protected array $message = [],
    ) {
    }

    protected function getData(string $key = ''): mixed
    {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }

    protected function getMessage(string $key, string $default = '验证失败'): string
    {
        return $this->message[$key] ?? $default;
    }

    public function validated(bool $batch = false): array
    {
        $funcName = $batch ? 'batchValidate' : 'validate';
        return $this->$funcName()->valid();
    }

    public function validate(): static
    {
        return $this->do(function($ruleName, $key, $value, $ruleParams) {
            if ($this->{$ruleName}($key, $value, ...$ruleParams)) {
                $this->valid[$key] = $value;
            }
        });
    }

    protected function do(callable $callable): static
    {
        if (!$this->validated) {
            $this->error = new Error();

            foreach ($this->rules as $key => $rule) {
                $value = $this->getData($key);
                if (!is_array($rule)) {
                    $rule = explode('|', $rule);
                }
                foreach ($rule as $ruleItem) {
                    $ruleItem   = explode(':', $ruleItem, 2);
                    $ruleName   = $ruleItem[0];
                    $ruleParams = empty($ruleItem[1]) ? [] : explode(',', $ruleItem[1]);
                    call_user_func_array($callable, [$ruleName, $key, $value, $ruleParams]);
                }
            }
            $this->validated = true;
        }

        return $this;
    }

    public function batchValidate(): static
    {
        return $this->do(function($ruleName, $key, $value, $ruleParams) {
            try {
                if ($this->{$ruleName}($key, $value, ...$ruleParams)) {
                    $this->valid[$key] = $value;
                }
            } catch (ValidateException) {
            }
        });
    }

    public function errors(): Error
    {
        return $this->error;
    }

    public function valid(): array
    {
        return $this->valid;
    }

    public function fails(): bool
    {
        return !$this->error->isEmpty();
    }

    public function failed(): array
    {
        return $this->error->all();
    }
}
