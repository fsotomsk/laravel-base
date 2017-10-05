<?php
/**
 * User: fso
 * Date: 05.05.2017
 * Time: 15:11
 */

namespace App\Framework;

use Illuminate\Database\Migrations\Migration as CMigration;
use Illuminate\Support\Facades\DB;

class Migration extends CMigration
{
    /**
     * @var Blueprint
     */
    protected $schema = null;

    protected $dbConnection = null;

    public function __construct()
    {
        $this->dbConnection = DB::connection();
        $this->schema = $this->dbConnection->getSchemaBuilder();

        $this->schema->blueprintResolver(function($table, $callback) {
            return new Blueprint($table, $callback);
        });
    }

    public function statement($query)
    {
        try {
            DB::statement($query);
        } catch (\Exception $e){}
    }

    public function table($table, $callback)
    {
        try {
            $this->schema->table($table, $callback);
        } catch (\Exception $e){}
    }

    public function create($table, $callback)
    {
        try {
            $this->schema->create($table, $callback);
        } catch (\Exception $e){}
    }

    public function dropIfExists($table)
    {
        try {
            $this->schema->dropIfExists($table);
        } catch (\Exception $e){}
    }

}