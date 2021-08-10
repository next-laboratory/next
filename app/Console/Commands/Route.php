<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Max\Console\Command;
use Max\Console\Exception\InvalidOptionException;
use Max\Console\Style;
use Max\Http\Route\Alias;

class Route extends Command
{

    /**
     * 缓存文件
     * @var string
     */
    protected $cacheFile;

    protected const SEPARATOR = "+------+----------------------------------------------+--------------------------------------------------+----------------+\n";

    /**
     * 初始化配置
     */
    public function configure()
    {
        $this->setName('route')
            ->setDescription('Manage your routers');
        $this->cacheFile = env('storage_path') . 'cache' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'routes.php';
    }

    public function exec()
    {
        $input = $this->input;
        if (!$input->hasParameters() || $input->hasArgument('--help') || $input->hasArgument('-H')) {
            echo $this->help();
            exit;
        }
        if ($input->hasArgument('--list') || $input->hasArgument('-l')) {
            return $this->list();
        }
        if ($input->hasArgument('--cache')) {
            if ($input->hasArgument('-d')) {
                return $this->deleteCache();
            }
            return $this->createCache();
        }
        throw new InvalidOptionException("Use `php max route --help` or `php max route -H` to look up for usable options.");
    }

    public function help()
    {
        $name = str_pad("php max {$this->name} [option]", 33, ' ', STR_PAD_RIGHT);
        return <<<EOT
\033[33m{$name}\033[0m           {$this->getDescription()}
Options:
          -l,        --list                 List all routers           
          -H,        --help                 Show helper             
          --cache [-d]                      Create a cache file for the route  
                                            Use -d to delete cached route files

EOT;
    }

    /**
     * 路由列表输出
     */
    public function list()
    {
        \Max\Facade\Route::register();
        echo self::SEPARATOR . "|" . $this->_format(' 请求', 6) . " |" . $this->_format('请求地址', 50) . "|" . $this->_format('路由地址', 54) . "|  " . $this->_format('别名', 16) . "|\n" . self::SEPARATOR;
        foreach (\Max\Facade\Route::all() as $method => $routes) {
            foreach ($routes as $route => $locate) {
                if (!isset($locate['route'])) {
                    continue;
                }
                $location = $locate['route'];
                if (is_array($location)) {
                    $location = implode('@', $location);
                } else if ($location instanceof \Closure || 'C:' === substr($location, 0, 2)) {
                    $location = '\Closure';
                }
                echo '|' . $this->_format(strtoupper($method), 6) . '|' . $this->_format($route, 46) . '|' . $this->_format($location, 50) . '| ' . $this->_format(app(Alias::class)->getAliasByUri($route), 15) . "|\n";
            }
        }
        exit(self::SEPARATOR);
    }

    /**
     * 生成路由缓存
     * 因为php串行化闭包问题，如果路由中存在闭包会报错
     */
    public function createCache()
    {
        if (!file_exists(dirname($this->cacheFile))) {
            mkdir(dirname($this->cacheFile), 0755, true);
        }
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
        \Max\Facade\Route::register();
        $routes = \Max\Facade\Route::all();
        foreach ($routes as $method => $route) {
            foreach ($route as $path => $location) {
                if (!isset($location['route'])) {
                    continue;
                }
                if ($location['route'] instanceof \Closure) {
                    $routes[$method][$path]['route'] = \Opis\Closure\serialize($location['route']);
                }
            }
        }
        file_put_contents($this->cacheFile, serialize(array_filter($routes)));
        return $this->writeLine('缓存生成成功!', Style::COLOR_GREEN);
    }

    /**
     * 删除路由缓存
     */
    public function deleteCache()
    {
        if (!file_exists($this->cacheFile)) {
            return $this->writeLine('没有缓存文件!', Style::STYLE_RB);
        }
        unlink($this->cacheFile);
        return $this->writeLine('缓存清除成功!', Style::COLOR_GREEN)->end();
    }


    /**
     * 格式化文本，给两端添加空格
     * @param $string
     * @param $length
     * @return string
     */
    private function _format($string, $length)
    {
        return str_pad($string, $length, ' ', STR_PAD_BOTH);
    }

}