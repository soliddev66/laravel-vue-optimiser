<?php

namespace App\Vngodev;

use Illuminate\Support\Facades\DB;

class ResourceImporter
{
    /**
     * Inserts or updates the given resource.
     *
     * @param       $table
     * @param       $rows
     * @param array $exclude The attributes to exclude in case of update.
     */
    public function insertOrUpdate($table, $rows, array $exclude = [])
    {
        // We assume all rows have the same keys so we arbitrarily pick one of them.
        $columns = array_keys($rows[0]);

        $columnsString = implode('`,`', $columns);
        $values = $this->buildSQLValuesFrom($rows);
        $updates = $this->buildSQLUpdatesFrom($columns, $exclude);
        $params = $this->array_flatten($rows);

        $query = "insert into {$table} (`{$columnsString}`) values {$values} on duplicate key update {$updates}";

        $sth = DB::getPdo()->prepare($query);
        $sth->execute($params);
        $id = DB::getPdo()->lastInsertID();

        return $id;
    }

    /**
     * Build proper SQL string for the values.
     *
     * @param array $rows
     * @return string
     */
    protected function buildSQLValuesFrom(array $rows)
    {
        $values = collect($rows)->reduce(function ($valuesString, $row) {
            return $valuesString .= '(' . rtrim(str_repeat('?,', count($row)), ',') . '),';
        }, '');

        return rtrim($values, ',');
    }

    /**
     * Build proper SQL string for the on duplicate update scenario.
     *
     * @param       $columns
     * @param array $exclude
     * @return string
     */
    protected function buildSQLUpdatesFrom($columns, array $exclude)
    {
        $updateString = collect($columns)->reject(function ($column) use ($exclude) {
            return in_array($column, $exclude);
        })->reduce(function ($updates, $column) {
            return $updates .= "`{$column}`=VALUES(`{$column}`),";
        }, '');

        return trim($updateString, ',');
    }

    public function array_flatten($array)
    {
        $return = [];

        array_walk_recursive($array, function ($x) use (&$return) {
            $return[] = $x;
        });

        return $return;
    }
}
