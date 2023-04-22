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

abstract class Context
{
    protected array $values = [];

    /**
     * @var array<callable>
     */
    protected array $handlers = [];

    public function use(callable ...$handlers): static
    {
        if (!empty($handlers)) {
            $this->handlers = [...$this->handlers, ...$handlers];
        }

        return $this;
    }

    abstract public function getRequestMethod(): string;

    abstract public function getPath(): string;

    abstract public function setParameters(array $parameters);

    final public function next(): void
    {
        if (count($this->handlers) === 0) {
            throw new RuntimeException('There is no handler that can be executed');
        }
        array_shift($this->handlers)($this);
    }

    public function abort(string $message = '', int $code = 0)
    {
        throw new Abort($message, $code);
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
