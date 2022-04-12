<?php

namespace Max\Utils\Traits;

trait AutoFillProperties
{
    /**
     * 使用数组填充属性
     *
     * @param array $properties
     * @param bool  $force
     */
    protected function fillProperties(array $properties, bool $force = false)
    {
        foreach ($properties as $key => $value) {
            if ($force || property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
