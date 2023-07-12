<?php

declare(strict_types=1);

/**
 * This file is part of MarxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Contract;

interface ConfigInterface
{
    public function getDSN(): string;

    public function getUser(): string;

    public function getPassword(): string;

    public function getOptions(): array;
}
