<?php

namespace Max\Utils;

use Max\Aop\AstManager;
use Symfony\Component\Finder\Finder;

class Classes
{
    public static function findInDirs(array $dirs): array
    {
        return array_keys(self::findWithPathInDirs($dirs));
    }

    public static function findWithPathInDirs(array $dirs): array
    {
        $files = (new Finder())->in($dirs)->name('*.php')->files();
        $classes = [];
        $astManager = new AstManager();
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach ($astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }
}