<?php

namespace LaravelEnso\VueDatatable\app\Classes\Table\Computors;

class Enum
{
    private static $columns;

    public static function compute($row)
    {
        foreach (self::$columns as $column) {
            $class = $column->get('enum');
            $enum = new $class();

            if (! is_null($row[$column->get('name')])) {
                $row[$column->get('name')] = $enum::get($row[$column->get('name')]);
            }
        }

        return $row;
    }

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->has('enum');
        });
    }
}
