<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Resource;

use JsonSerializable;
use Max\Utils\Collection;
use Max\Utils\Contract\Arrayable;

class ResourceCollection implements JsonSerializable, Arrayable
{
    public function __construct(
        protected Collection $resources
    ) {
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->resources->toArray();
    }
}
