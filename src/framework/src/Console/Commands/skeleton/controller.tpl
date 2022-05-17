<?php

declare(strict_types=1);

namespace {{namespace}};

use Max\Routing\Annotations\Controller;
use Max\Routing\Annotations\GetMapping;

#[Controller(prefix: '/{{path}}')]
class {{class}}
{
    #[GetMapping(path: '/')]
    public function index()
    {
    }
}
