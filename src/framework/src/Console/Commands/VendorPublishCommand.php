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

use Max\Aop\Scanner;
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
        $path   = getcwd();
        $config = Scanner::scanConfig($path . '/vendor/composer/installed.json');
        if (isset($config['publish'])) {
            foreach ($config['publish'] as $publish) {
                $destination = $publish['destination'];
                if (!file_exists($destination)) {
                    copy($publish['source'], $publish['destination']);
                    $output->writeln('<info>[DEBUG]</info> Package `' . $publish['name'] . '` config file published.');
                }
            }
        }
        $path .= '/runtime/app/';
        file_exists($path) || mkdir($path);
        file_put_contents($path . 'config.php', sprintf("<?php\n\nreturn %s;", var_export($config, true)));
        return 0;
    }
}
