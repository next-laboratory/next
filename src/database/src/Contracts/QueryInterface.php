<?php

namespace Max\Database\Contracts;

interface QueryInterface
{
    public function statement(string $query, array $bindings = []);
}
