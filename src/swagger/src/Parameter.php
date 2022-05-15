<?php

namespace Max\Swagger;

class Parameter implements \JsonSerializable
{
    /**
     * @param string $name
     * @param string $type 类型，可以是 "string", "number", "boolean", "integer", "array"
     * @param bool $required
     * @param string $in
     * @param string $description
     */
    public function __construct(
        protected string $name,
        protected string $type,
        protected bool   $required = false,
        protected string $in = 'query',
        protected string $description = ''
    )
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'required' => $this->required,
            'description' => $this->description,
            'in' => $this->in,
        ];
    }

}
