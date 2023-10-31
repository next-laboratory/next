<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Utils\Traits;

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
