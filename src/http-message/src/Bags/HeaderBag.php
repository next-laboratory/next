<?php

namespace Max\Http\Message\Bags;

use InvalidArgumentException;

class HeaderBag extends ParameterBag
{
    /**
     * @var array
     */
    protected array $map = [];

    /**
     * @param $value
     *
     * @return array|string[]
     */
    protected function formatValue($value): array
    {
        if (is_scalar($value)) {
            $value = [(string)$value];
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('The given header cannot be set.');
        }

        return array_values($value);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return mixed
     */
    public function get(string $key, $default = []): mixed
    {
        if ($this->has($key)) {
            return $this->parameters[$this->map[strtoupper($key)]];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
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

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->map[strtoupper($key)]);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        if ($this->has($key)) {
            $uppercaseKey = strtoupper($key);
            $key          = $this->map[$uppercaseKey];
            unset($this->parameters[$key]);
            unset($this->map[$uppercaseKey]);
        }
    }

    /**
     * @param array $parameters
     *
     * @return void
     */
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
            array_push($this->parameters[$this->map[$uppercaseKey]], ...(array)$value);
        } else {
            $this->map[$uppercaseKey] = $key;
            $this->parameters[$key]   = $this->formatValue($value);
        }
    }
}
