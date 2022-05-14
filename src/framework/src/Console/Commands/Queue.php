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
use Max\Container\Exceptions\NotFoundException;
use ReflectionException;

class Queue extends Command
{
    /**
     * @var string
     */
    protected string $name = 'queue:work';

    /**
     * @var string
     */
    protected string $description = 'Start the queue';

    /**
     * @return void
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function run()
    {
        $queue = new \Max\Queue\Queue(config('queue'));
        $queue->work($this->input->getOption('--queue') ?? null);
    }
}
