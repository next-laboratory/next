<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Database\Contracts;

use Max\Database\DatabaseConfig;

interface ConnectorInterface
{
    public function __construct(DatabaseConfig $config);

    public function get();
}
