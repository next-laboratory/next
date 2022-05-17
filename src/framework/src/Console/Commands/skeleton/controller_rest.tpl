<?php

declare(strict_types=1);

namespace {{namespace}};

use Max\Routing\Annotations\Controller;
use Max\Routing\Annotations\GetMapping;
use Max\Routing\Annotations\DeleteMapping;
use Max\Routing\Annotations\RequestMapping;

#[Controller(prefix: '/{{path}}')]
class {{class}}
{
    #[GetMapping(path: '/')]
    public function index()
    {
    }

    #[GetMapping(path: '/<id>')]
    public function show($id)
    {
    }

    #[DeleteMapping(path: '/<id>')]
    public function delete($id)
    {
    }

    #[RequestMapping(path: '/<id>', methods: ['PUT', 'PATCH'])]
    public function update($id)
    {
    }
}
