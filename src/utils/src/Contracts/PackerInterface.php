<?php

namespace Max\Utils\Contracts;

interface PackerInterface
{
    public function pack($data): string;

    public function unpack(string $data);
}