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

abstract class JsonResource implements JsonSerializable, Arrayable, \ArrayAccess, \Stringable
{
    public function __construct(
        protected $resource
    ) {
    }

    public function __get(string $name)
    {
        return $this->offsetGet($name);
    }

    public function __set(string $name, $value): void
    {
        $this->resource->offsetSet($name, $value);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public static function collect($resources): ResourceCollection
    {
        return new ResourceCollection((new Collection($resources))->transform(fn ($resource) => new static($resource)));
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset)
    {
        return isset($this->resource[$offset]);
    }

    public function offsetGet(mixed $offset)
    {
        return $this->resource[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value)
    {
        $this->resource[$offset] = $value;
    }

    public function offsetUnset(mixed $offset)
    {
        unset($this->resource[$offset]);
    }
}
