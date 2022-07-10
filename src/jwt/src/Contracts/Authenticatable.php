<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JWT\Contracts;

interface Authenticatable
{
    public function getIdentifier(): mixed;

    public function getClaims(): array;
}
