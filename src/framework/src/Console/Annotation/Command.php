<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Console\Annotation;

use Attribute;

/**
 * @deprecated Use IsCommand.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
}
