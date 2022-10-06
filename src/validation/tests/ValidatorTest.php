<?php

declare(strict_types=1);

/**
 * This file is part of MaxPHP.
 *
 * @link     https://github.com/marxphp
 * @license  https://github.com/marxphp/max/blob/master/LICENSE
 */

namespace Max\ValidatorTests;

use Max\Validator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
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
