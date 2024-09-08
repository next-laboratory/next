<?php

namespace Next\Aop;

use Composer\Autoload\ClassLoader;
use Exception;

class Composer
{
    /**
     * @throws Exception
     */
    public static function getClassLoader(): ClassLoader
    {
        $loaders = spl_autoload_functions();
        foreach ($loaders as $autoloadFunction) {
            if (is_array($autoloadFunction) && ($loader = $autoloadFunction[0]) instanceof ClassLoader) {
                return $loader;
            }
        }
        throw new Exception('Cannot find any composer class loader');
    }
}