<?php

namespace Max\Swagger;

use Max\Di\Scanner;
use Max\Swagger\Api\Api;

class Swagger
{
    /**
     * @param array  $scanDir
     * @param string $version
     * @param string $output
     */
    public function __construct(
        protected array  $scanDir = [],
        protected string $version = '',
        protected string $output = __DIR__
    )
    {
    }

    /**
     * @param array $scanDir
     */
    public function setScanDir(array $scanDir): void
    {
        $this->scanDir = $scanDir;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * 生成json
     *
     * @throws \ReflectionException
     */
    public function generateJson()
    {
        $classes = [];
        foreach ($this->scanDir as $dir) {
            $classes = array_merge(Scanner::scanDir($dir));
        }
        $paths = [];
        foreach ($classes as $class) {
            $reflectionClass = new \ReflectionClass($class);
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                foreach ($reflectionMethod->getAttributes() as $reflectionAttribute) {
                    if (($instance = $reflectionAttribute->newInstance()) instanceof Api) {
                        /** @var Api $instance */
                        $paths[$instance->getPath()] = [
                            $instance->getMethod() => $instance,
                        ];
                    }
                }
            }
        }

        file_put_contents($this->output, json_encode([
            'paths'   => $paths,
            "swagger" => "2.0",
            "info"    => [
                'title'   => '测试',
                'version' => $this->version,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
