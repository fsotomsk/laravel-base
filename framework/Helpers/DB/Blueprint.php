<?php

namespace CDeep\Helpers\DB;

use Illuminate\Database\Schema\Blueprint as CBlueprint;
use Illuminate\Support\Facades\Schema;

class Blueprint extends CBlueprint
{

    /**
     * Add nullable creation and update timestamps to the table.
     *
     * @param int $precision
     */
    public function timestamps($precision = 0)
    {
        $this->timestamp(Model::CREATED_AT, $precision)->nullable();
        $this->timestamp(Model::UPDATED_AT, $precision)->nullable();
    }

    /**
     * Indicate that the timestamp columns should be dropped.
     *
     * @return void
     */
    public function dropTimestamps()
    {
        $this->dropColumn(Model::CREATED_AT, Model::UPDATED_AT);
    }


    /**
     * Add creation and update timestampTz columns to the table.
     *
     * @param int $precision
     */
    public function timestampsTz($precision = 0)
    {
        $this->timestampTz(Model::CREATED_AT, $precision)->nullable();
        $this->timestampTz(Model::UPDATED_AT, $precision)->nullable();
    }

    /**
     * Drop columns if exists
     *
     * @param $columns
     *
     * @return void
     */
    public function dropColumnIfExists($columns)
    {
        $columns = is_array($columns) ? $columns : (array) func_get_args();
        foreach ($columns as $column) {
            if(Schema::hasColumn($this->table, $column)) {
                parent::dropColumn($column);
            }
        }
    }
}