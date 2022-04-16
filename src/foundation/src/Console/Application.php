<?php
declare(strict_types=1);

namespace Max\Foundation\Console;

use Max\Config\Repository;
use Max\Console\Application as ConsoleApplication;
use Max\Console\Exceptions\UnNamedCommandException;
use Max\Console\Output\ConsoleOutput;
use Max\Di\Container;
use Max\Di\Context;
use Max\Di\Exceptions\NotFoundException;
use Max\Di\Scanner;
use Max\Env\Env;
use Max\Env\Loader\IniFileLoader;
use Max\Event\EventDispatcher;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class Application extends ConsoleApplication
{
    /**
     * 注解扫描路径
     *
     * @var array
     */
    protected array $scanDir = [];

    /**
     * @throws UnNamedCommandException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(protected EventDispatcher $eventDispatcher)
    {
        parent::__construct();
        $this->initConfig();
        $this->initBindings();
        $this->initCommands();
        $this->initListeners();
        Scanner::init();
    }

    protected function initCommands()
    {
        array_unshift($this->scanDir, __DIR__ . '/Commands');
        foreach ($this->scanDir as $dir) {
            foreach (Scanner::scanDir($dir) as $command) {
                try {
                    $this->add(make($command));
                } catch (\Throwable $throwable) {
                    make(ConsoleOutput::class)->error($throwable->getMessage());
                }
            }
        }
    }

    protected function initBindings()
    {
        $container  = Context::getContainer();
        $repository = $container->make(Repository::class);
        $bindings   = $repository->get('di.bindings', []);
        $configFile = base_path('runtime/app/config.php');
        if (file_exists($configFile)) {
            $config   = require $configFile;
            $bindings = array_merge($config['bindings'] ?? [], $bindings);
        }
        foreach ($bindings ?? [] as $id => $binding) {
            $container->alias($id, $binding);
        }
    }

    protected function initConfig()
    {
        /** @var Container $container */
        $container = Context::getContainer();
        /** @var Env $env */
        $env = $container->make(Env::class);
        $env->load(new IniFileLoader('./.env'));
        /** @var Repository $repository */
        $repository = $container->make(Repository::class);
        $repository->load(glob(base_path('config/*.php')));
    }

    protected function initListeners()
    {
        $listenerProvider = $this->eventDispatcher->getListenerProvider();
        foreach (config('event.listeners') as $listener) {
            $listenerProvider->addListener(make($listener));
        }
    }
}
