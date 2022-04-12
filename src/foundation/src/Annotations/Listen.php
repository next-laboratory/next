<?php
declare(strict_types=1);

namespace Max\Foundation\Annotations;

use Attribute;
use Max\Di\Annotations\ClassAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class Listen extends ClassAnnotation
{
}
