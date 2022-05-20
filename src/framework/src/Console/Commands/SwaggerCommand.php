<?php

namespace Max\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwaggerCommand extends Command
{
    protected function configure()
    {
        $this->setName('swagger');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $dir     = $input->getArguments()[1] ?? __DIR__;
        $swagger = new \Max\Swagger\Swagger([BASE_PATH . '/app/Http/Controllers']);
        $swagger->setOutput($dir);
        $swagger->generateJson();
        return 0;
    }

}
