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

namespace Max\Validator\Bags;

class Parameter
{
    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @param string $error
     *
     * @return $this
     */
    public function push(string $error): static
    {
        $this->items[] = $error;

        return $this;
    }

    /**
     * @return mixed
     */
    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }
}
