<?php

namespace Next\Watcher\Driver;

use Next\Watcher\Contract\DriverInterface;
use Symfony\Component\Finder\Finder;

class FindDriver implements DriverInterface
{
    /**
     * @var \SplFileInfo[]
     */
    protected array $last = [];

    public function __construct(
        protected array $dirs,
        protected \Closure $callback,
        protected string $pattern = '*.php',
        protected int $interval = 1000000,
    ) {
        foreach ($this->findFiles() as $file) {
            $this->last[$file->getRealPath()] = $file->getMTime();
        }
    }

    public function watch(): void
    {
        while (true) {
            usleep($this->interval);
            $currentFiles = [];
            $modified     = $added = [];
            foreach ($this->findFiles() as $file) {
                $realPath  = $file->getRealPath();
                $fileMTime = $file->getMTime();
                if (!isset($this->last[$realPath])) {
                    $added[]               = $realPath;
                    $this->last[$realPath] = $fileMTime;
                } else {
                    if ($this->last[$realPath] != $fileMTime) {
                        $modified[]            = $realPath;
                        $this->last[$realPath] = $fileMTime;
                    }
                }

                $currentFiles[$realPath] = $fileMTime;
            }
            $deleted    = array_diff_key($this->last, $currentFiles);
            $this->last = array_diff_key($this->last, $deleted);
            $deleted    = array_keys($deleted);
            clearstatcache();
            if (!empty($modified) || !empty($added) || !empty($deleted)) {
                ($this->callback)($added, $modified, $deleted);
            }
        }
    }

    protected function findFiles(): Finder
    {
        return Finder::create()->in($this->dirs)->name($this->pattern)->files();
    }
}
