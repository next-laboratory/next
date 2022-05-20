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

class QueueCommand extends Command
{
    protected function configure()
    {
        $this->setName('queue:work')
             ->setDescription('Start the queue');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = new \Max\Queue\Queue(config('queue'));
        $queue->work($input->getOption('--queue') ?? null);
    }
}
