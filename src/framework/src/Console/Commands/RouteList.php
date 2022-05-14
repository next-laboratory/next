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

use Closure;
use Max\Console\Commands\Command;
use Max\Container\Exceptions\NotFoundException;
use Max\Routing\Route;
use Max\Routing\RouteCollector;
use Max\Utils\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

class RouteList extends Command
{
    /**
     * @var string
     */
    protected string $name = 'route:list';

    /**
     * @var string
     */
    protected string $description = 'List your routes';

    protected const SEPARATOR = "+---------------------------+------------------------------------------------------------+---------------------------------------------+----------------+\n";

    /**
     * @return void
     * @throws NotFoundException
     * @throws ReflectionException|ContainerExceptionInterface
     */
    public function run()
    {
        echo self::SEPARATOR . "|" . $this->format(' Methods', 26) . " |" . $this->format('URI', 60) . "|" . $this->format('Action', 45) . "|     Domain     |\n" . self::SEPARATOR;
        foreach ($this->getRoutes() as $route) {
            /** @var Route $route */
            $action = $route->getAction();
            if (is_array($action)) {
                $action = implode('@', $action);
            } else if ($action instanceof Closure) {
                $action = 'Closure';
            }
            echo '|' . $this->format(strtoupper(implode('|', $route->getMethods())), 27) . '|' . $this->format($route->getPath(), 60) . '|' . $this->format($action, 45) . '| ' . $this->format(($route->getDomain() ?: '*'), 15) . "|\n";
        }
        echo self::SEPARATOR;
    }

    /**
     * @return Collection
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws ReflectionException
     */
    protected function getRoutes(): Collection
    {
        /** @var RouteCollector $routeCollector */
        make(RequestHandlerInterface::class);
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
        return collect($routes)->unique()->sortBy(function($item) {
            /** @var Route $item */
            return $item->getPath();
        });
    }

    /**
     * 格式化文本，给两端添加空格
     *
     * @param $string
     * @param $length
     *
     * @return string
     */
    private function format($string, $length): string
    {
        return str_pad($string, $length, ' ', STR_PAD_BOTH);
    }
}
