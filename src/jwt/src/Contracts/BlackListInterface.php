<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JWT\Contracts;

interface BlackListInterface
{
    public function add(string $token);

    public function isIn(string $token): bool;

    public function remove(string $token);
}
