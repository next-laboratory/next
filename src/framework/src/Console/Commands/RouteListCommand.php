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

use App\Http\Kernel;
use Closure;
use Max\Di\Exceptions\NotFoundException;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class RouteListCommand extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('route:list')
             ->setDescription('List the routes');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table(new ConsoleOutput());
        $table->setHeaders(['Methods', 'URI', 'Action', 'Middlewares', 'Domain']);
        foreach ($this->getRoutes() as $route) {
            /** @var Route $route */
            $action = $route->getAction();
            if (is_array($action)) {
                $action = implode('@', $action);
            } else if ($action instanceof Closure) {
                $action = 'Closure';
            }
            $table->addRow([
                implode('|', $route->getMethods()),
                $route->getPath(),
                $action,
                implode(PHP_EOL, $route->getMiddlewares()),
                $route->getDomain() ?: '*'
            ]);
        }
        $table->render();
        return 0;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function getRoutes(): Collection
    {
        make(Kernel::class);
        $routeCollector = make(RouteCollector::class);
        $routes         = [];
        foreach ($routeCollector->all() as $registeredRoute) {
            foreach ($registeredRoute as $route) {
                foreach ($route as $item) {
                    if (!in_array($item, $routes)) {
                        $routes[] = $item;
                    }
                }
            }
        }
        return Collection::make($routes)->unique()->sortBy(function($item) {
            /** @var Route $item */
            return $item->getPath();
        });
    }
}
