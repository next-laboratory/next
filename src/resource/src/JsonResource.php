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

abstract class JsonResource implements JsonSerializable, Arrayable
{
    public function __construct(
        protected $resource
    ) {
    }

    public static function collect($resources): ResourceCollection
    {
        return new ResourceCollection((new Collection($resources))->transform(fn ($resource) => new static($resource)));
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
