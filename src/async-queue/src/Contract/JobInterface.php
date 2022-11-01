<?php

namespace Max\AsyncQueue\Contract;

interface JobInterface
{
    public function handle(): void;

    public function getAttempts(): int;

    public function run(): void;
}
