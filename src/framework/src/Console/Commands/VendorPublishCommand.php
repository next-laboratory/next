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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class VendorPublishCommand extends Command
{
    protected function configure()
    {
        $this->setName('vendor:publish')
             ->setDescription('Publish publishable packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path      = getcwd();
        $installed = json_decode(file_get_contents($path . '/vendor/composer/installed.json'), true);
        $installed = $installed['packages'] ?? $installed;
        $config    = [];
        foreach ($installed as $package) {
            if (isset($package['extra']['max']['config'])) {
                $provider       = $package['extra']['max']['config'];
                $configProvider = new $provider;
                if (method_exists($configProvider, '__invoke')) {
                    if (is_array($configItem = $configProvider())) {
                        $output->writeln('<info>[DEBUG]</info> Package `' . $provider . '` metadata cached.');
                        $config = array_merge_recursive($config, $configItem);
                    }
                }
                if (method_exists($configProvider, 'publish')) {
                    try {
                        $configProvider->publish();
                        $output->writeln('<info>[DEBUG]</info> Package `' . $provider . '` publish successfully.');
                    } catch (Throwable $throwable) {
                        $output->writeln('<comment>[WAGNING]</comment> Package `' . $provider . '` publish failed.' . $throwable->getMessage());
                    }
                }
            }
        }
        $path .= '/runtime/app/';
        file_exists($path) || mkdir($path);
        file_put_contents($path . 'config.php', sprintf("<?php\n\nreturn %s;", var_export($config, true)));
        return 0;
    }
}
