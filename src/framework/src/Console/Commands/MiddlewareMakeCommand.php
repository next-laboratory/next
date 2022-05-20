<?php

namespace Max\Framework\Console\Commands;

use Max\Utils\Exceptions\FileNotFoundException;
use Max\Utils\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MiddlewareMakeCommand extends Command
{
    protected string $stubsPath = __DIR__ . '/stubs/';

    protected function configure()
    {
        $this->setName('make:middleware')
             ->setDescription('Making middleware.')
             ->setDefinition([
                 new InputArgument('middleware', InputArgument::REQUIRED, 'A middleware name such as `auth`.'),
                 new InputOption('suffix', 's', InputOption::VALUE_OPTIONAL, 'File is suffixed when this option is available.')
             ]);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws FileNotFoundException|\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stubFile = $this->stubsPath . 'middleware.stub';
        [$namespace, $middleware] = $this->parse($input->getArgument('middleware'));
        $middlewarePath = base_path('app/Http/Middlewares/' . str_replace('\\', '/', $namespace) . '/');
        $fileSystem     = new Filesystem();
        $fileSystem->exists($middlewarePath) || $fileSystem->makeDirectory($middlewarePath, 0755, true);
        $suffix         = $input->getOption('suffix') ? 'Middleware' : '';
        $middlewareFile = $middlewarePath . $middleware . $suffix . '.php';
        $fileSystem->exists($middlewareFile) && throw new \InvalidArgumentException('中间件已经存在！');
        $fileSystem->put($middlewareFile, str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Middlewares' . $namespace, $middleware . $suffix], file_get_contents($stubFile)));
        $output->writeln("<info>[DEBU]</info>中间件App\\Http\\Middlewares{$namespace}\\{$middleware}创建成功！");

        return 1;
    }

    /**
     * @param $input
     *
     * @return array
     */
    protected function parse($input): array
    {
        $array     = explode('/', $input);
        $class     = ucfirst(array_pop($array));
        $namespace = implode('\\', array_map(fn($value) => ucfirst($value), $array));
        if (!empty($namespace)) {
            $namespace = '\\' . $namespace;
        }
        return [$namespace, $class];
    }
}
