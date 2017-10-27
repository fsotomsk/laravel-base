<?php

namespace CDeep\Helpers\DB;


use Illuminate\Database\Migrations\Migration as CMigration;
use Illuminate\Support\Facades\DB;

class Migration extends CMigration
{
    /**
     * @var Blueprint
     */
    protected $schema = null;

    /**
     * @var \Illuminate\Database\Connection
     */
    protected $dbConnection = null;

    /**
     * Migration constructor.
     */
    public function __construct()
    {
        $this->dbConnection = DB::connection();
        $this->schema = $this->dbConnection->getSchemaBuilder();

        $this->schema->blueprintResolver(function($table, $callback) {
            return new Blueprint($table, $callback);
        });
    }

    /**
     * @var array
     */
    protected static $modelTablesCache = [];

    /**
     * @param $model
     * @return mixed
     */
    public function getModelTable($model)
    {
        if (isset(self::$modelTablesCache[$model])) {
            return self::$modelTablesCache[$model];
        }

        return self::$modelTablesCache[$model] = (function() use ($model){
            if (class_exists($model)) {
                $model = new $model();
                if ($model instanceof \Illuminate\Database\Eloquent\Model) {
                    return $model->getTable();
                }
            }
            return null;
        })();
    }

    /**
     * @param \Exception $e
     */
    protected function exception(\Exception $e)
    {
        if (method_exists($this, 'error')) {
            $this->error($e->getMessage());
        } else {
            echo "{$e->getMessage()}\n";
        }
    }

    /**
     * @param $query
     */
    public function statement($query)
    {
        try {
            DB::statement($query);
        } catch (\Exception $e){
            $this->exception($e);
        }
    }

    /**
     * @param $table
     * @param $callback
     */
    public function table($table, $callback)
    {
        try {
            $this->schema->table($table, $callback);
        } catch (\Exception $e){
            $this->exception($e);
        }
    }

    /**
     * @param $table
     * @param $callback
     */
    public function create($table, $callback)
    {
        try {
            $this->schema->create(
                $this->getModelTable($table) ?: $table,
                $callback
            );
        } catch (\Exception $e){
            $this->exception($e);
        }
    }

    /**
     * @param $table
     */
    public function dropIfExists($table)
    {
        try {
            $this->schema->dropIfExists(
                $this->getModelTable($table) ?: $table
            );
        } catch (\Exception $e){
            $this->exception($e);
        }
    }

}