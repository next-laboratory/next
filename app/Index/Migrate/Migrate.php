<?php


namespace app\index\migrate;


class Migrate extends \yao\migrate\Migrate
{
    public function createTable()
    {
        $this->table('uers')
            ->addColumn('username', 'varchar(10)')
            ->addColumn('password', 'int');
    }
}