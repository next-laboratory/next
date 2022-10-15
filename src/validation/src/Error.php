<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

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
