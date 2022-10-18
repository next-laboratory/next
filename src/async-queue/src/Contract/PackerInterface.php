<?php

namespace Max\AsyncQueue\Contract;

interface PackerInterface
{
    public function unpack(string $data): JobInterface;

    public function pack(JobInterface $job): string;
}
