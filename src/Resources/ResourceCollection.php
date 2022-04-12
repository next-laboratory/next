<?php

namespace Max\Utils\Resources;

use JsonSerializable;
use ReturnTypeWillChange;

class ResourceCollection implements JsonSerializable
{
    /**
     * @param $collection
     */
    public function __construct(public $collection)
    {
    }

    /**
     * @return string
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->collection;
    }
}
