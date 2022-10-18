<?php

namespace Max\AsyncQueue\Packer;

use Max\AsyncQueue\Contract\JobInterface;
use Max\AsyncQueue\Contract\PackerInterface;
use Max\AsyncQueue\Exception\InvalidJobException;

class PhpSerializePacker implements PackerInterface
{
    /**
     * @throws InvalidJobException
     */
    public function unpack(string $data): JobInterface
    {
        $job = @unserialize($data);
        if (!$job instanceof JobInterface) {
            throw new InvalidJobException('The job must be an instance of JobInterface');
        }

        return $job;
    }

    public function pack(JobInterface $job): string
    {
        return @serialize($job);
    }
}
