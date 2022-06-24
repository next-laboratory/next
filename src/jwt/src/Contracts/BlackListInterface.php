<?php

namespace Max\JWT\Contracts;

interface BlackListInterface
{
    public function add(string $token);

    public function isIn(string $token): bool;

    public function remove(string $token);
}
