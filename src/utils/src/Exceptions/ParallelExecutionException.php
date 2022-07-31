<?php

declare(strict_types=1);

namespace Max\Utils\Exception;

class ParallelExecutionException extends \RuntimeException
{
    private array $results;
    private array $throwables;

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results)
    {
        $this->results = $results;
    }

    public function getThrowables(): array
    {
        return $this->throwables;
    }

    public function setThrowables(array $throwables): array
    {
        return $this->throwables = $throwables;
    }
}
