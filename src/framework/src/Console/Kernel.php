<?php

namespace Max\Framework\Console;

use Composer\Autoload\ClassLoader;
use Max\Config\Repository;
use Max\Console\Application;
use Max\Di\Container;
use Max\Di\Context;
use Max\Di\Scanner;
use Max\Env\Env;
use Max\Env\Loader\IniFileLoader;
use Max\Event\EventDispatcher;
use Max\Event\ListenerCollector;

class Kernel extends Application
{
    public function init(ClassLoader $loader)
    {
        /** @var Container $container */
        $container = Context::getContainer();
        /** @var Env $env */
        $env = $container->make(Env::class);
        $env->load(new IniFileLoader('./.env'));
        /** @var Repository $repository */
        $repository = $container->make(Repository::class);
        $repository->load(glob(base_path('config/*.php')));
        $bindings = $repository->get('di.bindings', []);

        $installed = json_decode(file_get_contents(BASE_PATH . '/vendor/composer/installed.json'), true);
        $installed = $installed['packages'] ?? $installed;
        $config    = [];
        foreach ($installed as $package) {
            if (isset($package['extra']['max']['config'])) {
                $configProvider = $package['extra']['max']['config'];
                $configProvider = new $configProvider;
                if (method_exists($configProvider, '__invoke')) {
                    if (is_array($configItem = $configProvider())) {
                        $config = array_merge_recursive($config, $configItem);
                    }
                }
            }
        }
        $bindings = array_merge($config['bindings'] ?? [], $bindings);

        foreach ($bindings ?? [] as $id => $binding) {
            $container->bind($id, $binding);
        }

        Scanner::init($loader, $repository->get('di.scanner'));
        foreach ($config['commands'] ?? [] as $command) {
            $this->add(new $command());
        }
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher  = $container->make(EventDispatcher::class);
        $listenerProvider = $eventDispatcher->getListenerProvider();
        foreach (ListenerCollector::getListeners() as $listener) {
            $listenerProvider->addListener($container->make($listener));
        }
        foreach (\Max\Console\CommandCollector::all() as $command) {
            $this->add(new $command);
        }
    }
}
