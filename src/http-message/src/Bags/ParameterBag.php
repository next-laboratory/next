<?php

namespace Max\HttpMessage\Bags;

class ParameterBag
{
    /**
     * @var array
     */
    protected array $parameters = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->replace($parameters);
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * @param array $parameters
     *
     * @return void
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->parameters;
    }
}
