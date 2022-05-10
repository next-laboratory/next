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

use Max\Console\Commands\Command;
use Throwable;

class VendorPublish extends Command
{
    /**
     * @var string
     */
    protected string $name = 'vendor:publish';

    /**
     * @var string
     */
    protected string $description = 'Publish publishable packages';

    /**
     * @return void
     */
    public function run()
    {
        $path      = getcwd();
        $installed = json_decode(file_get_contents($path . '/vendor/composer/installed.json'), true);
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
                if (method_exists($configProvider, 'publish')) {
                    try {
                        $configProvider->publish();
                        $this->output->debug('Publish successfully.');
                    } catch (Throwable $throwable) {
                        $this->output->error($throwable->getMessage());
                    }
                }
            }
        }
        $path .= '/runtime/app/';
        file_exists($path) || mkdir($path);
        file_put_contents($path . 'config.php', sprintf("<?php\n\nreturn %s;", var_export($config, true)));
    }
}
