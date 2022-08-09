<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\Utils;

use Max\Utils\Exception\ParallelExecutionException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;

class Parallel
{
    /**
     * @var callable[]
     */
    private array $callbacks = [];

    /**
     * @var null|Channel
     */
    private $concurrentChannel;

    /**
     * @param int $concurrent if $concurrent is equal to 0, that means unlimit
     */
    public function __construct(int $concurrent = 0)
    {
        if ($concurrent > 0) {
            $this->concurrentChannel = new Channel($concurrent);
        }
    }

    public function add(callable $callable, $key = null): static
    {
        if (is_null($key)) {
            $this->callbacks[] = $callable;
        } else {
            $this->callbacks[$key] = $callable;
        }
        return $this;
    }

    public function wait(bool $throw = true): array
    {
        $result = $throwables = [];
        $wg     = new WaitGroup();
        $wg->add(count($this->callbacks));
        foreach ($this->callbacks as $key => $callback) {
            $this->concurrentChannel && $this->concurrentChannel->push(true);
            Coroutine::create(function () use ($callback, $key, $wg, &$result, &$throwables) {
                try {
                    $result[$key] = call($callback);
                } catch (\Throwable $throwable) {
                    $throwables[$key] = $throwable;
                } finally {
                    $this->concurrentChannel && $this->concurrentChannel->pop();
                    $wg->done();
                }
            });
        }
        $wg->wait();
        if ($throw && ($throwableCount = count($throwables)) > 0) {
            $message            = 'Detecting ' . $throwableCount . ' throwable occurred during parallel execution:' . PHP_EOL . $this->formatThrowables($throwables);
            $executionException = new ParallelExecutionException($message);
            $executionException->setResults($result);
            $executionException->setThrowables($throwables);
            throw $executionException;
        }
        return $result;
    }

    public function count(): int
    {
        return count($this->callbacks);
    }

    public function clear(): void
    {
        $this->callbacks = [];
    }

    /**
     * Format throwables into a nice list.
     *
     * @param \Throwable[] $throwables
     */
    private function formatThrowables(array $throwables): string
    {
        $output = '';
        foreach ($throwables as $key => $value) {
            $output .= \sprintf('(%s) %s: %s' . PHP_EOL . '%s' . PHP_EOL, $key, get_class($value), $value->getMessage(), $value->getTraceAsString());
        }
        return $output;
    }
}
