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

use JsonSerializable;
use Max\Utils\Collection;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated
 */
class Pagination implements JsonSerializable
{
    /**
     * @var Collection
     */
    protected Collection $resources;

    /**
     * @var int
     */
    protected int $total;

    /**
     * @var int
     */
    protected int $page;

    /**
     * @var int
     */
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

    /**
     * @return Collection
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerpage(): int
    {
        return $this->perpage;
    }
}
