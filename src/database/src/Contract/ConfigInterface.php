<?php

namespace Max\Database\Contract;

interface ConfigInterface
{
    public function getDSN(): string;

    public function getUser(): string;

    public function getPassword(): string;

    public function getOptions(): array;
}