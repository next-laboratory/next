<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils\Traits;

trait AutoFillProperties
{
    /**
     * 使用数组填充属性.
     */
    protected function fillProperties(array $properties, bool $force = false): void
    {
        foreach ($properties as $key => $value) {
            if ($force || property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
