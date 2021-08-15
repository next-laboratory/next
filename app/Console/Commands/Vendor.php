<?php

namespace App\Console\Commands;

use Max\Console\Command;

class Vendor extends Command
{

    protected $name = 'vendor:publish';

    protected $description = 'Publish publishable packages';

    public function exec()
    {
        $installed = json_decode(file_get_contents(getcwd() . '/vendor/composer/installed.json'), true);
        $installed = $installed['packages'] ?? $installed;
        $path      = getcwd();
        foreach ($installed as $package) {
            if (isset($package['extra']['max']['config']) && is_array($config = $package['extra']['max']['config'])) {
                foreach ($config as $dir => $file) {
                    $configFile = "{$path}/config/" . basename($file);
                    if (!file_exists($configFile)) {
                        if (@copy("{$path}/vendor/max/{$dir}/{$file}", $configFile)) {
                            echo "\033[32mGenerate config file successfully: {$configFile} \033[0m \n";
                        } else {
                            echo "\033[31mGenerate config file failed: {$configFile} \033[0m \n";
                        }
                    }
                }
            }
        }
    }

}
