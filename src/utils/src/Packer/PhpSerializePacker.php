<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils\Packer;

use Next\Utils\Contract\PackerInterface;

class PhpSerializePacker implements PackerInterface
{
    public function pack($data): string
    {
        return \serialize($data);
    }

    public function unpack(string $data)
    {
        return \unserialize($data);
    }
}
