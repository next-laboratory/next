<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Contract;

interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}
