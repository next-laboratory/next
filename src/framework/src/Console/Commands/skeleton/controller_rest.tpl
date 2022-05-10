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

    #[GetMapping(path: '/<id>')]
    public function show($id) {
        //
    }

    #[DeleteMapping(path: '/<id>')]
    public function delete($id) {
        //
    }

    #[RequestMapping(path: '/<id>', methods: ['PUT', 'PATCH'])]
    public function update() {
        //
    }
}
