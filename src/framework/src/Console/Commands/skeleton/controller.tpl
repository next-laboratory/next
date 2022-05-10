<?php

declare(strict_types=1);

namespace {{namespace}};

use Max\Http\Annotations\Controller;
use Max\Http\Annotations\GetMapping;
use Max\Http\Annotations\DeleteMapping;
use Max\Http\Annotations\RequestMapping;

#[Controller(prefix: '/')]
class {{class}}
{
    #[GetMapping(path: '/')]
    public function index() {
        //
    }
}
