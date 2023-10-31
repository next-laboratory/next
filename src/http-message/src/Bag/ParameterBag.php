<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message\Bag;

class ParameterBag
{
    protected array $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->replace($parameters);
    }

    public function get(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    public function set(string $key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function remove(string $key)
    {
        unset($this->parameters[$key]);
    }

    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    public function all(): array
    {
        return $this->parameters;
    }
}
