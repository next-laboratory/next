<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\Console\Commands;

use Max\Console\Application;
use Max\Console\Contracts\InputInterface;
use Max\Console\Contracts\OutputInterface;

abstract class Command
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $help = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * @var bool
     */
    protected bool $enable = true;

    /**
     * @var Application|null
     */
    protected ?Application    $application = null;
    protected InputInterface  $input;
    protected OutputInterface $output;

    /**
     * @return bool
     */
    public function hasName(): bool
    {
        return isset($this->name);
    }

    /**
     * @return Application|null
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * @param Application|null $application
     */
    public function setApplication(?Application $application = null)
    {
        $this->application = $application;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $help
     */
    public function setHelp(string $help): void
    {
        $this->help = $help;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Command
     */
    public function setInputAndOutput(InputInterface $input, OutputInterface $output): static
    {
        $this->input  = $input;
        $this->output = $output;

        return $this;
    }

    /**
     * @return void
     */
    abstract public function run();
}
