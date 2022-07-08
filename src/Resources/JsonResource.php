<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Utils\Resources;

use ArrayAccess;
use JsonSerializable;
use Max\Utils\Collection;
use Max\Utils\Contracts\Arrayable;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated
 */
abstract class JsonResource implements Arrayable, JsonSerializable, ArrayAccess
{
    protected int $perpage = 15;
    protected int $page    = 1;
    protected const PAGE    = 'page';
    protected const PERPAGE = 'perpage';

    /**
     * @var
     */
    protected $resource;

    /**
     * @param $resource
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource;
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
        return new ResourceCollection($resources->map(function($resource) {
            return new static($resource);
        }));
    }

    /**
     * 分页
     *
     * @param                             $resources
     * @param ServerRequestInterface|null $request
     *
     * @return Pagination
     */
    public static function paginate($resources, ?ServerRequestInterface $request = null): Pagination
    {
        if (!$resources instanceof Collection) {
            $resources = Collection::make($resources);
        }
        $resource = new static();
        $page     = $resource->getPage();
        $perpage  = $resource->getPerpage();
        if (!is_null($request)) {
            $page    = $request->getParsedBody()[static::PAGE] ?? ($request->getQueryParams()[static::PAGE] ?? $page);
            $perpage = $request->getParsedBody()[static::PERPAGE] ?? ($request->getQueryParams()[static::PERPAGE] ?? $perpage);
        }
        $page    = max(0, (int)$page);
        $perpage = max(0, (int)$perpage);
        return new Pagination($resources->forPage($page, $perpage)->map(fn($resource) => new static($resource))->values(), $resources->count(), $page, $perpage, $request);
    }

    /**
     * @return int
     */
    public function getPerpage(): int
    {
        return $this->perpage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public
    function __get(string $name)
    {
        return $this->resource->{$name};
    }

    /**
     * @return array
     */
    #[
        \ReturnTypeWillChange]
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
