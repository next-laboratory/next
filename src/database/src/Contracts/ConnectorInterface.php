<?php

namespace Max\Database\Contracts;

use Max\Database\DatabaseConfig;

interface ConnectorInterface
{
    public function __construct(DatabaseConfig $config);

    public function get();
}
