<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Cache;

use ArrayObject;
use InvalidArgumentException;
use Max\Config\Contracts\ConfigInterface;

class CacheManager
{
    protected array       $config;

    protected string      $defaultStore;

    protected ArrayObject $stores;

    public function __construct(ConfigInterface $config)
    {
        $config             = $config->get('cache');
        $this->defaultStore = $config['default'];
        $this->config       = $config['stores'];
        $this->stores       = new ArrayObject();
    }

    public function store(?string $name = null)
    {
        $name ??= $this->defaultStore;
        if (! $this->stores->offsetExists($name)) {
            if (! isset($this->config[$name])) {
                throw new InvalidArgumentException('配置不正确');
            }
            $config  = $this->config[$name];
            $handler = $config['handler'];
            $this->stores->offsetSet($name, new ($handler)($config['options']));
        }
        return $this->stores->offsetGet($name);
    }
}
