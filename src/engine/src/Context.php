<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Engine;

use RuntimeException;

class Context
{
    protected array $values = [];

    /**
     * @var array<callable>
     */
    protected array $handlers = [];

    final public function withHandlers(callable ...$handlers): static
    {
        if (!empty($handlers)) {
            $this->handlers = [...$this->handlers, ...$handlers];
        }

        return $this;
    }

    final public function next(): void
    {
        if (count($this->handlers) === 0) {
            throw new RuntimeException('There is no handler that can be executed');
        }
        array_shift($this->handlers)($this);
    }

    final public function abort(string $message = '', int $code = 0)
    {
        throw new Abort($message, $code);
    }

    final public function setValues(array $values): void
    {
        $this->values = $values;
    }

    final public function setValue(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    final public function hasValue(string $key): bool
    {
        return isset($this->values[$key]);
    }

    final public function getValue(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }
}
