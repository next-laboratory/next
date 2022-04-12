<?php

namespace Max\Utils\Packer;

use Max\Utils\Contracts\PackerInterface;
use function json_decode;
use function json_encode;

class JsonPacker implements PackerInterface
{
    /**
     * @param $data
     *
     * @return string
     */
    public function pack($data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    public function unpack(string $data)
    {
        return json_decode($data, true);
    }
}
