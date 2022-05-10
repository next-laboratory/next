<?php

declare (strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Framework\Console\Commands;

use Exception;
use Max\Console\Commands\Command;
use Max\Console\Exceptions\InvalidOptionException;
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
    protected string $skeletonPath = __DIR__ . '/skeleton/';

    /**
     * @return void
     * @throws InvalidOptionException|FileNotFoundException
     * @throws Exception
     */
    public function run()
    {
        if ($this->input->hasArgument('--help') || $this->input->hasArgument('-H')) {
            echo $this->getHelp();
        } else {
            if ($this->input->hasOption('-c')) {
                $this->makeController();
            } else if ($this->input->hasOption('-mw')) {
                $this->makeMiddleware();
            } else {
                throw new InvalidOptionException('Use `php max make --help` or `php max make -H` to look up for usable options.');
            }
        }

    }

    /**
     * @return void
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function makeController(): void
    {
        $file = $this->skeletonPath . ($this->input->hasArgument('--rest') ? 'controller_rest.tpl' : 'controller.tpl');
        [$namespace, $controller] = $this->parse($this->input->getOption('-c'));

        $path           = base_path('app/Http/Controllers/' . str_replace('\\', '/', $namespace) . '/');
        $fileSystem     = new Filesystem();
        $controllerFile = $path . $controller . 'Controller.php';
        if ($fileSystem->exists($controllerFile)) {
            $this->output->warning('控制器已经存在!');
            return;
        }

        $fileSystem->exists($path) || $fileSystem->makeDirectory($path, 0777, true);
        $fileSystem->put($controllerFile, str_replace(['{{namespace}}', '{{class}}', '{{path}}'], ['App\\Http\\Controllers' . $namespace, $controller . 'Controller', strtolower($controller)], $fileSystem->get($file)));
        $this->output->debug("控制器App\\Http\\Controllers{$namespace}\\{$controller}Controller创建成功！");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function makeMiddleware(): void
    {
        $file = $this->skeletonPath . 'middleware.tpl';
        [$namespace, $middleware] = $this->parse($this->input->getOption('-mw'));
        $stream     = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Middlewares' . $namespace, $middleware], file_get_contents($file));
        $path       = base_path('app/Http/Middlewares/' . str_replace('\\', '/', $namespace) . '/');
        $fileSystem = new Filesystem();
        $fileSystem->exists($path) || $fileSystem->makeDirectory($path, 0777, true);
        $fileSystem->put($path . $middleware . '.php', $stream);
        $this->output->debug("中间件App\\Http\\Middlewares{$namespace}\\{$middleware}创建成功！");
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
