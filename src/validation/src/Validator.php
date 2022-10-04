<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validation;

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
        protected array $messages = [],
        protected bool $stopOnFirstFailure = true,
    ) {
    }

    protected function getData(string $key = ''): mixed
    {
        return $key ? ($this->data[$key] ?? null) : $this->data;
    }

    protected function getMessage(string $key, string $default = '验证失败'): string
    {
        return $this->messages[$key] ?? $default;
    }

    /**
     * @throws ValidationException
     */
    public function validated(): array
    {
        return $this->validate()->valid();
    }

    public function validate(): static
    {
        return $this->passes();
    }

    public function passes(): static
    {
        if (!$this->validated) {
            $this->error = new Error();
            foreach ($this->rules as $key => $rule) {
                if (!is_array($rule)) {
                    $rule = explode('|', $rule);
                }
                $value = $this->getData($key);
                foreach ($rule as $ruleItem) {
                    [$ruleName, $ruleParams] = RuleParser::parse($ruleItem);
                    if ($this->{$ruleName}($key, $value, ...$ruleParams)) {
                        $this->valid[$key] = $value;
                    }
                }
            }
            $this->validated = true;
        }
        return $this;
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
