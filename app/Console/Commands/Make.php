<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Max\Facade\File;
use Max\Console\Command;
use Max\Console\Style;
use Max\Console\Exception\InvalidOptionException;

class Make extends Command
{

    protected $name = 'make';

    protected $description = 'Create files in command line';

    protected $skeletonPath;

    public function __construct()
    {
        $this->skeletonPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR;
    }

    public function exec()
    {
        $input = $this->input;
        if (!$input->hasParameters() || $input->hasArgument('--help') || $input->hasArgument('-H')) {
            echo $this->help();
        }
        if ($input->hasOption('-c')) {
            return $this->controller();
        }
        if ($input->hasOption('-m')) {
            return $this->model();
        }
        if ($input->hasOption('-mw')) {
            return $this->middleware();
        }
        if ($input->hasOption('-r')) {
            return $this->request();
        }
        throw new InvalidOptionException('Use `php max make --help` or `php max make -H` to look up for usable options.');
    }

    public function controller()
    {
        $file = $this->skeletonPath . ($this->input->hasArgument('--rest') ? 'controller_rest.tpl' : 'controller.tpl');
        [$namespace, $controller] = $this->parse($this->input->getOption('-c'));

        $path = env('app_path') . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;

        $controllerFile = $path . $controller . '.php';
        if (file_exists($controllerFile)) {
            return $this->writeLine('控制器已经存在!', Style::STYLE_RB);
        }

        File::exists($path) || File::mkdir($path);

        $file = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Controllers' . $namespace, $controller], file_get_contents($file));
        file_put_contents($path . $controller . '.php', $file);
        return $this->writeLine("控制器App\\Http\\Controllers{$namespace}\\{$controller}创建成功！", Style::STYLE_GB);
    }

    /**
     * 创建模型
     * @return \Max\Console\Output
     */
    public function model()
    {
        return $this->writeLine("暂时不支持创建模型！", Style::STYLE_RB);
    }

    public function middleware()
    {
        $file = $this->skeletonPath . 'middleware.tpl';
        [$namespace, $middleware] = $this->parse($this->input->getOption('-mw'));
        $stream = str_replace(['{{namespace}}', '{{class}}'], ['App\\Http\\Middleware' . $namespace, $middleware], file_get_contents($file));
        $path   = env('app_path') . 'Http' . DIRECTORY_SEPARATOR . 'Middleware' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        File::exists($path) || File::mkdir($path);
        file_put_contents($path . $middleware . '.php', $stream);
        return $this->writeLine("中间件App\\Http\\Middleware{$namespace}\\{$middleware}创建成功！", Style::STYLE_GB);
    }

    public function request()
    {
        return $this->writeLine("暂时不支持创建请求类！", Style::STYLE_RB);
    }

    protected function parse($input)
    {
        $array     = explode('/', $input);
        $class     = ucfirst(array_pop($array));
        $namespace = implode('\\', array_map(function ($value) {
            return ucfirst($value);
        }, $array));
        if (!empty($namespace)) {
            $namespace = '\\' . $namespace;
        }
        return [$namespace, $class];
    }

    public function help()
    {
        $name = str_pad("php max {$this->name} [option]", 33, ' ', STR_PAD_RIGHT);
        return <<<EOT
\033[33m{$name}\033[0m           {$this->getDescription()}
Options:  
          -c  <controller> [--rest]         Create a controller file (php max make -c index/index)
                                            Use [--rest] to create a restful controller
          -m  <model>
          -mw <middleware>                  Create a middleware file (php max make -mw UACheck)
          -r  <request>

EOT;
    }
}
