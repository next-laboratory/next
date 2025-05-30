<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace Next\Aop;

use Next\Aop\Collector\PropertyAttributeCollector;

trait PropertyHandler
{
    protected bool $__propertyHandled = false;

    protected function __handleProperties(): void
    {
        if (! $this->__propertyHandled) {
            foreach (PropertyAttributeCollector::getByClass(self::class) as $property => $attributes) {
                foreach ($attributes as $attribute) {
                    $attribute->handle($this, $property);
                }
            }
            $this->__propertyHandled = true;
        }
    }
}
