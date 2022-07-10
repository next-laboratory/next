<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Validator\Bags;

class Parameter
{
    protected array $items = [];

    /**
     * @return $this
     */
    public function push(string $error): static
    {
        $this->items[] = $error;

        return $this;
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function all(): array
    {
        return $this->items;
    }
}
