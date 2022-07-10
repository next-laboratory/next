<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Watcher;

use Symfony\Component\Finder\Finder;

class Watcher
{
    public function watch(array $dirs, string $pattern, callable $callback, float $interval = 1)
    {
        $original = [];
        $files    = Finder::create()->in($dirs)->name($pattern)->files();
        foreach ($files as $file) {
            $original[$file->getRealPath()] = $file->getMTime();
        }

        echo 'Watching changed files.' . PHP_EOL;

        while (true) {
            sleep($interval);
            clearstatcache();
            $modified = [];
            $files    = Finder::create()->in($dirs)->name($pattern)->files();
            foreach ($files as $file) {
                $realPath  = $file->getRealPath();
                $fileMTime = $file->getMTime();
                if (! isset($original[$realPath])) {
                    $original[$realPath] = $fileMTime;
                    $modified[]          = $realPath;
                } else {
                    if ($original[$realPath] != $fileMTime) {
                        $original[$realPath] = $fileMTime;
                        $modified[]          = $realPath;
                    }
                }
            }
            if (! empty($modified)) {
                $callback($modified);
            }
        }
    }
}
