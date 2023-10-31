<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Http\Message\Bag;

class CookieBag extends ParameterBag
{
    protected array $map = [];

    public function replace(array $parameters = [])
    {
        $this->parameters = array_change_key_case($parameters, CASE_UPPER);
    }

    public function get(string $key, $default = null): mixed
    {
        return parent::get(strtoupper($key), $default);
    }

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
