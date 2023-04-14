<?php

declare(strict_types=1);

namespace Max\Pipeline;

use RuntimeException;

class Context
{
    protected array $values;

    /**
     * @var array<callable>
     */
    protected array $pipes = [];

    /**
     * @var callable
     */
    protected $endPoint = null;

    public function use(callable ...$pipes): static
    {
        array_push($this->pipes, ...$pipes);

        return $this;
    }

    public function final(callable $endPoint): static
    {
        $this->endPoint = $endPoint;

        return $this;
    }

    final public function next(): void
    {
        if (count($this->pipes) === 0) {
            if (is_null($this->endPoint)) {
                throw new RuntimeException('the final method is null or has been executed');
            }
            $endPoint       = $this->endPoint;
            $this->endPoint = null;
            $endPoint($this);
        } else {
            $pipe = array_shift($this->pipes);
            $pipe($this);
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

    public function getValues(): array
    {
        return $this->values;
    }
}