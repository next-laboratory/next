<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Pipeline;

class Context
{
    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array<callable>
     */
    protected $handlers = [];

    /**
     * @return $this
     */
    final public function withHandlers(callable ...$handlers): Context
    {
        if (! empty($handlers)) {
            array_push($this->handlers, ...$handlers);
        }

        return $this;
    }

    final public function next()
    {
        if (count($this->handlers) === 0) {
            throw new \RuntimeException('There is no handler that can be executed');
        }
        array_shift($this->handlers)($this);
    }

    /**
     * @throws Abort
     */
    final public function abort(string $message = '', int $code = 0)
    {
        throw new Abort($message, $code);
    }

    final public function setValues(array $values)
    {
        $this->values = $values;
    }

    final public function setValue(string $key, mixed $value)
    {
        $this->values[$key] = $value;
    }

    final public function hasValue(string $key): bool
    {
        return isset($this->values[$key]);
    }

    /**
     * @return mixed
     */
    final public function getValue(string $key)
    {
        return $this->values[$key] ?? null;
    }
}
