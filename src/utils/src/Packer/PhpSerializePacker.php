<?php

namespace Max\Utils\Packer;

use Max\Utils\Contracts\PackerInterface;
use function serialize;
use function unserialize;

class PhpSerializePacker implements PackerInterface
{
    /**
     * @param $data
     *
     * @return string
     */
    public function pack($data): string
    {
        return serialize($data);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    public function unpack(string $data)
    {
        return unserialize($data);
    }
}
