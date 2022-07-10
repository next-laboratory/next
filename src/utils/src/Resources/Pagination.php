<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Resources;

use JsonSerializable;
use Max\Utils\Collection;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated
 */
class Pagination implements JsonSerializable
{
    protected Collection $resources;

    protected int $total;

    protected int $page;

    protected int $perpage;

    /**
     * @param Collection $resources 集合
     * @param int        $total     总数
     * @param int        $page      当前页
     * @param int        $perpage   分页数量
     */
    public function __construct(Collection $resources, int $total, int $page = 1, int $perpage = 15, protected ?ServerRequestInterface $request = null)
    {
        $this->resources = $resources;
        $this->total     = $total;
        $this->page      = $page;
        $this->perpage   = $perpage;
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'data' => $this->getResources(),
            'meta' => [
                'total'   => $this->getTotal(),
                'page'    => $this->getPage(),
                'perpage' => $this->getPerpage(),
            ],
        ];
    }

    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerpage(): int
    {
        return $this->perpage;
    }
}
