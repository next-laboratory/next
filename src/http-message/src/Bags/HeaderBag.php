<?php

namespace Max\HttpMessage\Bags;

use InvalidArgumentException;

class HeaderBag extends ParameterBag
{
    protected array $map = [];

    protected function formatValue($value)
    {
        if (is_scalar($value)) {
            $value = [(string)$value];
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('The given header cannot be set.');
        }

        return array_values($value);
    }

    public function get(string $key, $default = []): mixed
    {
        if ($this->has($key)) {
            return $this->parameters[$this->map[strtoupper($key)]];
        }
        return $default;
    }

    public function set(string $key, $value)
    {
        $this->map[strtoupper($key)] = $key;
        $this->parameters[$key] = $this->formatValue($value);
    }

    public function has(string $key): bool
    {
        return isset($this->map[strtoupper($key)]);
    }

    public function remove(string $key)
    {
        if ($this->has($key)) {
            $uppercaseKey = strtoupper($key);
            $key = $this->map[$uppercaseKey];
            unset($this->parameters[$key]);
            unset($this->map[$uppercaseKey]);
        }
    }

    public function replace(array $parameters = [])
    {
        $this->parameters = [];
        $this->map = [];
        foreach ($parameters as $key => $value) {
            $this->map[strtoupper($key)] = $key;
            $this->parameters[$key] = $this->formatValue($value);
        }
    }
}