<?php

declare(strict_types=1);

namespace {{namespace}};

use Max\Http\Annotations\Controller;
use Max\Http\Annotations\GetMapping;

#[Controller(prefix: '/')]
class {{class}}
{
    #[GetMapping(path: '/')]
    public function index() {
        //
    }
}
