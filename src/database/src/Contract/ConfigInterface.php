<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Database\Contract;

interface ConfigInterface
{
    public function getDSN(): string;

    public function getUser(): string;

    public function getPassword(): string;

    public function getOptions(): array;
}
