<?php

namespace Max\Http\Message\Bags;

class CaseInsensitiveBag extends ParameterBag
{
    protected array $map = [];

    /**
     * @param array $parameters
     *
     * @return void
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = array_change_key_case($parameters, CASE_UPPER);
        $this->map        = array_combine(array_keys($this->parameters), $parameters);
    }

    /**
     * @param string $key
     * @param        $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        return parent::get(strtoupper($key), $default);
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function set(string $key, $value)
    {
        parent::set(strtoupper($key), $value);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return parent::has(strtoupper($key));
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function remove(string $key)
    {
        parent::remove(strtoupper($key));
    }
}
