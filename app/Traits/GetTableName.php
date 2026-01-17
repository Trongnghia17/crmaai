<?php

namespace App\Traits;


trait GetTableName
{
    protected $table;

    public static function getTableName(): string
    {
        return (new self())->getTable();
    }

    public function getTable()
    {
        return $this->table;
    }
}
