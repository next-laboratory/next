<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Aop;

use Max\Utils\Traits\AutoFillProperties;

class ScannerConfig
{
    use AutoFillProperties;

    protected bool $cache = false;

    protected array $scanDirs = [];

    protected array $collectors = [];

    protected string $runtimeDir = '';

    public function __construct(array $options)
    {
        $this->fillProperties($options);
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function getScanDirs(): array
    {
        return $this->scanDirs;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getRuntimeDir(): string
    {
        return rtrim($this->runtimeDir, '/\\');
    }
}
