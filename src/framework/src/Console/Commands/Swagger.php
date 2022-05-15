<?php

namespace Max\Framework\Console\Commands;

use Max\Console\Commands\Command;

class Swagger extends Command
{
    protected string $name = 'swagger';

    public function run()
    {
        $dir = $this->input->getArguments()[1] ?? __DIR__;
        $swagger = new \Max\Swagger\Swagger([BASE_PATH . '/app/Http/Controllers']);
        $swagger->setOutput($dir);
        $swagger->generateJson();
    }
}