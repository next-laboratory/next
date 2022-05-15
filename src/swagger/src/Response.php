<?php

namespace Max\Swagger;

class Response implements \JsonSerializable
{
    /**
     * @param int $code
     * @param string $description
     * @param Schema|null $schema
     */
    public function __construct(
        protected int    $code,
        protected string $description = '',
        ?Schema          $schema = null
    )
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'description' => $this->description,
        ];
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this->code;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
