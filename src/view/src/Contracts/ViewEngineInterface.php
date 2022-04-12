<?php
declare(strict_types=1);

namespace Max\View\Contracts;

interface ViewEngineInterface
{
    public function setPath(string $path);

    public function render(string $template, array $arguments = []);
}
