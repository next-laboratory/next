<?php

namespace Max\ValidatorTests;

use Max\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate()
    {
        $data      = [
            'name'   => 'maxphp',
            'length' => 10,
        ];
        $rules     = [
            'name'   => 'length:2,3',
            'length' => 'integer',
        ];
        $validator = new Validator($data, $rules);
        $validator->validate();
    }
}
