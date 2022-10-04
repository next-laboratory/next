<?php

namespace Max\Validation;

class Error
{
    /**
     * @var string[]
     */
    protected array $errors = [];

    public function first(): string
    {
        return $this->errors[0] ?? '';
    }

    public function push(string $error): void
    {
        $this->errors[] = $error;
    }

    public function isEmpty(): bool
    {
        return count($this->errors) === 0;
    }

    public function all(): array
    {
        return $this->errors;
    }
}
