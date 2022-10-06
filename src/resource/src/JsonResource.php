<?php

namespace Max\Resource;

use JsonSerializable;
use Max\Utils\Collection;
use Max\Utils\Contract\Arrayable;

class JsonResource implements JsonSerializable, Arrayable
{
    public function __construct(
        protected $resource
    ) {
    }

    public static function collect($resources): ResourceCollection
    {
        return new ResourceCollection((new Collection($resources))->transform(fn($resource) => new static($resource)));
    }

    public function toArray(): array
    {
        return (array)$this->resource;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
