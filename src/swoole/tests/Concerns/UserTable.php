<?php

namespace Next\Swoole\Tests\Concerns;

use Next\Swoole\Table\Model;

class UserTable extends Model
{
    protected static string $table    = 'users';
    protected static array  $fillable = ['id', 'info'];
    protected static array  $casts    = ['info' => 'json'];
}