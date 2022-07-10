<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Http\Message\Bags;

class CaseInsensitiveBag extends ParameterBag
{
    protected array $map = [];

    public function replace(array $parameters = [])
    {
        $arrayKeys        = array_keys($parameters);
        $this->parameters = array_change_key_case($parameters, CASE_UPPER);
        $this->map        = array_combine(array_keys($this->parameters), $arrayKeys);
    }

    /**
     * @param $default
     */
    public function get(string $key, $default = null): mixed
    {
        return parent::get(strtoupper($key), $default);
    }

    /**
     * @param $value
     */
    public function set(string $key, $value)
    {
        parent::set(strtoupper($key), $value);
    }

    public function has(string $key): bool
    {
        return parent::has(strtoupper($key));
    }

    public function remove(string $key)
    {
        parent::remove(strtoupper($key));
    }
}
