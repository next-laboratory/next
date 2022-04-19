<?php

namespace Max\Utils\Resources;

use ArrayAccess;
use JsonSerializable;
use Max\Utils\Collection;
use Max\Utils\Contracts\Arrayable;

class JsonResource implements Arrayable, JsonSerializable, ArrayAccess
{
    /**
     * @param $resource
     */
    public function __construct(public $resource)
    {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->resource->toArray();
    }

    /**
     * @param $resources
     *
     * @return ResourceCollection
     */
    public static function collection($resources): ResourceCollection
    {
        if (!$resources instanceof Collection) {
            $resources = Collection::make($resources);
        }
        return new ResourceCollection($resources->map(function ($resource) {
            return new static($resource);
        }));
    }

    /**
     * 分页
     *
     * @param     $resuorces
     * @param int $page
     * @param int $perpage
     *
     * @return Pagination
     */
    public static function paginate($resuorces, int $page = 1, int $perpage = 15)
    {
        if (!$resuorces instanceof Collection) {
            $resuorces = Collection::make($resuorces);
        }

        return new Pagination($resuorces->forPage($page, $perpage)->map(fn($resource) => new static($resource)), $resuorces->count(), $page, $perpage);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->resource->{$name};
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->resource[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->resource[$offset] ?? null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->resource[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->resource[$offset]);
    }
}
