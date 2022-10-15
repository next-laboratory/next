<?php

namespace Max\Validation\Tests;

use Max\Validation\ValidationException;
use Max\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testRequired()
    {
        $validator = new Validator([
            'key' => 'foo',
        ], [
            'key' => 'required',
        ], stopOnFirstFailure: false);
        $validated = $validator->validated();
        $this->assertArrayHasKey('key', $validated);
        $validator = new Validator([
        ], [
            'key' => 'required',
        ], stopOnFirstFailure: false);
        $validated = $validator->validated();
        $this->assertArrayNotHasKey('key', $validated);
        $validator = new Validator([
        ], [
            'key' => 'required',
        ]);
        $e         = null;
        try {
            $validator->validate();
        } catch (ValidationException $e) {

        }
        $this->assertInstanceOf(ValidationException::class, $e);
    }
}