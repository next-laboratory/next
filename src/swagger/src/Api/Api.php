<?php

namespace Max\Swagger\Api;

use Max\Swagger\Parameter;
use Max\Swagger\Response;

abstract class Api implements \JsonSerializable
{
    protected string $method = '';

    /**
     * @param string      $path
     * @param array       $tags
     * @param string      $summary
     * @param Parameter[] $parameters
     * @param Response[]  $responses
     */
    public function __construct(
        protected string $path,
        protected array  $tags = [],
        protected string $summary = '',
        protected array  $parameters = [],
        protected array  $responses = []
    )
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $swagger      = [
            'tags'       => array_map(fn($value) => (string)$value, $this->tags),
            'summary'    => $this->summary,
            'parameters' => $this->parameters,
        ];
        $jsonResponse = [];
        foreach ($this->responses as $response) {
            $jsonResponse[$response->getCode()] = $response;
        }
        $swagger['responses'] = $jsonResponse;

        return $swagger;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
