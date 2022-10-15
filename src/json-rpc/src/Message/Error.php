<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\JsonRpc\Message;

use JsonSerializable;

use function get_object_vars;

class Error implements JsonSerializable
{
    public function __construct(
        protected int $code,
        protected string $message,
        protected ?array $data = null,
    ) {
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
