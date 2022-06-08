<?php

namespace Max\Http\Message\Bags;

class CaseInsensitiveBag extends ParameterBag
{
    /**
     * @param array $parameters
     *
     * @return void
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = array_change_key_case($parameters, CASE_UPPER);
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
