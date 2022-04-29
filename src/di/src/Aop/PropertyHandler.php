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

namespace Max\Di\Aop;

use Max\Di\Annotation\Collector\PropertyAttributeCollector;
use Max\Di\Exceptions\PropertyHandleException;
use Throwable;

trait PropertyHandler
{
    /**
     * @return void
     */
    protected function __handleProperties(): void
    {
        $class = static::class;
        foreach (PropertyAttributeCollector::getClassPropertyAttributes($class) as $property => $attributes) {
            try {
                foreach ($attributes as $attribute) {
                    $attribute->handle($property, $this);
                }
            } catch (Throwable $throwable) {
                throw new PropertyHandleException('Property handle failed. ' . $throwable->getMessage());
            }
        }
    }
}
