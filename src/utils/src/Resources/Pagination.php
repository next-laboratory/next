<?php

namespace Max\Utils\Resources;

use Max\Utils\Collection;

class Pagination implements \JsonSerializable
{
    /**
     * @param Collection $resources
     * @param int        $total
     * @param int        $page
     * @param int        $perpage
     */
    public function __construct(protected Collection $resources, protected int $total, protected int $page, protected int $perpage)
    {
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
                'links'   => [
                    'first'   => '',
                    'last'    => '',
                    'current' => '',
                    'next'    => '',
                    'end'     => '',
                ],
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