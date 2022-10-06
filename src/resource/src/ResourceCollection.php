<?php

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
