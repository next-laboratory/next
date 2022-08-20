<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Resource;

use JsonSerializable;

/**
 * @deprecated
 */
class ResourceCollection implements JsonSerializable
{
    /**
     * @param $collection
     */
    public function __construct(
        protected $collection
    ) {
    }

    /**
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->collection;
    }
}
