<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message\Bag;

class HeaderBag extends ServerBag
{
    protected array $map = [];

    public function get(string $key, $default = []): mixed
    {
        if ($this->has($key)) {
            return $this->parameters[$this->map[strtoupper($key)]];
        }
        return $default;
    }

    public function set(string $key, $value)
    {
        $uppercaseKey = strtoupper($key);
        if (isset($this->map[$key])) {
            $this->parameters[$this->map[$key]] = $this->formatValue($value);
        } else {
            $this->map[$uppercaseKey] = $key;
            $this->parameters[$key]   = $this->formatValue($value);
        }
    }

    public function has(string $key): bool
    {
        return isset($this->map[strtoupper($key)]);
    }

    public function remove(string $key)
    {
        if ($this->has($key)) {
            $uppercaseKey = strtoupper($key);
            $key          = $this->map[$uppercaseKey];
            unset($this->parameters[$key], $this->map[$uppercaseKey]);
        }
    }

    public function replace(array $parameters = [])
    {
        $this->parameters = [];
        $this->map        = [];
        foreach ($parameters as $key => $value) {
            $this->map[strtoupper($key)] = $key;
            $this->parameters[$key]      = $this->formatValue($value);
        }
    }

    public function add(string $key, $value)
    {
        $uppercaseKey = strtoupper($key);
        if (isset($this->map[$uppercaseKey])) {
            array_push($this->parameters[$this->map[$uppercaseKey]], ...(array) $value);
        } else {
            $this->map[$uppercaseKey] = $key;
            $this->parameters[$key]   = $this->formatValue($value);
        }
    }

    /**
     * @return array|string[]
     */
    protected function formatValue($value): array
    {
        if (is_scalar($value)) {
            $value = [(string) $value];
        }
        if (! is_array($value)) {
            throw new \InvalidArgumentException('The given header cannot be set.');
        }

        return array_values($value);
    }
}
