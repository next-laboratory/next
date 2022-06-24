<?php

namespace Max\JWT\Contracts;

interface Authenticatable
{
    public function getIdentifier(): mixed;

    public function getClaims(): array;
}
