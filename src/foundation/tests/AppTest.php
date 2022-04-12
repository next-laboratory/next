<?php

namespace Max\Tests;

use PHPUnit\Framework\TestCase;
use Max\Foundation\App;

class AppTest extends TestCase
{

    /**
     * @var App
     */
    protected $app;

    protected function setUp(): void
    {
        $this->app = new App();
    }

    

}
