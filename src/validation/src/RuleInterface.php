<?php

namespace Next\Validation;

interface RuleInterface
{
    public function setValue(mixed $value);

    public function valid(): bool;
}