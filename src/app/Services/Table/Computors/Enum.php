<?php

namespace LaravelEnso\Tables\app\Services\Table\Computors;

class Enum
{
    private static $columns;

    public static function columns($columns)
    {
        self::$columns = $columns->filter(function ($column) {
            return $column->get('enum');
        });
    }

    public static function handle($row)
    {
        foreach (self::$columns as $column) {
            $class = $column->get('enum');
            $enum = new $class();

            if ($row[$column->get('name')] !== null) {
                $row[$column->get('name')] = $enum::get($row[$column->get('name')]);
            }
        }

        return $row;
    }
}
