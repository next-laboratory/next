<?php
declare(strict_types=1);

namespace Max\Utils\Contracts;

interface Jsonable
{
    public function __toString(): string;
}
