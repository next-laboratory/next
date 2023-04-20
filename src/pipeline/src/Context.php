<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Pipeline;

use RuntimeException;

class Context
{
    protected array $values = [];

    /**
     * @var array<callable>
     */
    protected array $handlers = [];

    /**
     * @var callable
     */
    protected $final;

    public function use(callable ...$handlers): static
    {
        array_push($this->handlers, ...$handlers);

        return $this;
    }

    final public function next(): void
    {
        if (count($this->handlers) === 0) {
            throw new RuntimeException('There is no handler that can be executed');
        }
        $handler = array_shift($this->handlers);
        $handler($this);
    }

    public function setValue(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function hasValue(string $key): bool
    {
        return isset($this->values[$key]);
    }

    public function getValue(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }
}
