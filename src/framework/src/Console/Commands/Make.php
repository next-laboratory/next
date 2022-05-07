<?php
declare(strict_types=1);

namespace Max\Framework\Console\Commands;

use Exception;
use Max\Console\Commands\Command;
use Max\Console\Exceptions\InvalidOptionException;
use Max\Di\Annotation\Inject;
use Max\Utils\Exceptions\FileNotFoundException;
use Max\Utils\Filesystem;
use Psr\Http\Message\ServerRequestInterface;

class Make extends Command
{
    /**
     * @var string
     */
    protected string $name = 'make';

    /**
     * @var string
     */
    protected string $description = 'Create files in command line';

    /**
     * @var string
     */
    protected string $help = "-c  <controller> [--rest]                   Create a controller file (php max make -c index/index)
                                                Use [--rest] to create a restful controller
-mw <middleware>                            Create a middleware file (php max make -mw UACheck)";

    /**
     * @var string
     */
    protected string $skeletonPath = __DIR__ . '/../Commands/skeleton/';

    /**
     * @return void
     * @throws InvalidOptionException|FileNotFoundException
     */
    public function run()
    {
        if ($this->input->hasArgument('--help') || $this->input->hasArgument('-H')) {
            echo $this->getHelp();
        }
        if ($this->input->hasOption('-c')) {
            return $this->makeController();
        }
        if ($this->input->hasOption('-mw')) {
            return $this->makeMiddleware();
        }
        throw new InvalidOptionException('Use `php max make --help` or `php max make -H` to look up for usable options.');
    }

    /**
     * @return void
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function makeController()
    {
        $file = $this->skeletonPath . ($this->input->hasArgument('--rest') ? 'controller_rest.tpl' : 'controller.tpl');
        [$namespace, $controller] = $this->parse($this->input->getOption('-c'));

        $path           = base_path('app/Http/Controllers/' . str_replace('\\', '/', $namespace) . '/');
        $fileSystem     = new Filesystem();
        $controllerFile = $path . $controller . '.php';
        if ($fileSystem->exists($controllerFile)) {
            $this->output->warning('控制器已经存在!');
            return;
        }

        $fileSystem->exists($path) || $fileSystem->makeDirectory($path, 0777, true);

        $file = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Controllers' . $namespace, $controller], $fileSystem->get($file));
        $fileSystem->put($path . $controller . '.php', $file);
        $this->output->debug("控制器App\\Http\\Controllers{$namespace}\\{$controller}创建成功！");
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function makeMiddleware()
    {
        $file = $this->skeletonPath . 'middleware.tpl';
        [$namespace, $middleware] = $this->parse($this->input->getOption('-mw'));
        $stream = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Middlewares' . $namespace, $middleware], file_get_contents($file));
        $path   = base_path('app/Http/Middlewares/' . str_replace('\\', '/', $namespace) . '/');
        Filesystem::exists($path) || Filesystem::makeDirectory($path, 0777, true);
        file_put_contents($path . $middleware . '.php', $stream);
        return $this->output->debug("中间件App\\Http\\Middlewares{$namespace}\\{$middleware}创建成功！");
    }

    /**
     * @param $input
     *
     * @return array
     */
    protected function parse($input)
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
