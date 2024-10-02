<?php

namespace Next\Validation\Rules;

use Next\Validation\RuleInterface;

class In implements RuleInterface
{
    protected mixed $value;

    protected string $message = '';

    public function __construct(
        protected array $haystack
    )
    {
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function failure(): string
    {
        return $this->message;
    }

    public function valid(): bool
    {
        if (!$valid = in_array($this->value, $this->haystack)) {
            $this->message = '验证不通过';
        }

        return $valid;
    }
}