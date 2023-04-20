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

    protected int $handlerIndex = 0;

    protected bool $end = false;

    /**
     * @var callable
     */
    protected $final;

    public function use(callable ...$handlers): static
    {
        array_push($this->handlers, ...$handlers);

        return $this;
    }

    public function final(callable $final): static
    {
        $this->final = $final;

        return $this;
    }

    final public function next(): void
    {
        if (count($this->handlers) === $this->handlerIndex) {
            if (is_null($this->final)) {
                throw new RuntimeException('the final method is null or has been executed');
            }
            $this->end = true;
            ($this->final)($this);
        } else {
            $handler = $this->handlers[$this->handlerIndex++];
            $handler($this);
        }
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
