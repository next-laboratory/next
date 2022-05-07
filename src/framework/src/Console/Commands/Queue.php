<?php
declare(strict_types=1);

namespace Max\Framework\Console\Commands;

use Max\Console\Commands\Command;
use Max\Di\Exceptions\NotFoundException;
use ReflectionException;

#[\Max\Console\Annotations\Command]
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
