<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder as CSeeder;

class Seeder extends CSeeder
{

    protected $isDebug = false;
    protected $dataSet = 'production';

    /**
     * Seeder constructor.
     */
    public function __construct()
    {
        $this->isDebug = \App::environment('dev', 'testing', 'local');
        $this->dataSet = config('seeds.dataSet', \App::environment());
    }

    /**
     * @param $string
     * @return null|string
     */
    protected function strToDate($string)
    {
        return $string
            ? Carbon::createFromFormat('d.m.Y',$string)->format('Y-m-d')
            : null;
    }

    public function getDataSetPath($fileName=null)
    {
        return database_path("seeds/data/{$this->dataSet}/{$fileName}");
    }

    private $outdatedCache = [];
    protected function isOutdatedModel($model, $renewMd5=true)
    {
        if (isset($this->outdatedCache[$model])) {
            return $this->outdatedCache[$model];
        }

        if (class_exists($model)) {

            /**
             * @var \App\Framework\Model $instance
             */
            $instance = new $model();
            if ($instance instanceof \App\Framework\Model) {

                $table = $instance->getTable();
                $file  = $this->getDataSetFileName($table);

                if (file_exists($file)) {

                    $md5      = md5_file($file);
                    $md5file  = storage_path("app/seeds/{$table}.md5");
                    $outdated = file_exists($md5file)
                        ? (trim(file_get_contents($md5file)) != "{$md5}" || $model::count() == 0)
                        : true;

                    if ($outdated) {

                        $this->info("Model {$model} outdated! [{$md5}]");

                        if ($renewMd5) {
                            $dir = dirname($md5file);
                            if (!is_dir($dir)) {
                                mkdir($dir, 0755, true);
                            }
                            file_put_contents($md5file, $md5);
                        }
                    }

                    $this->outdatedCache[$model] = $outdated;
                    return $outdated;
                }
            }
        }

        return false;
    }

    public function getDataSetFileName($table)
    {
        return $this->getDataSetPath("{$table}.json");
    }

    public function getDataSetMd5FileName($table)
    {
        return storage_path("app/seeds/{$this->dataSet}/{$table}.md5");
    }

    /**
     * @param $table
     * @return array
     */
    protected function sourceJson($table)
    {
        $file = $this->getDataSetFileName($table);
        if (file_exists($file)) {
            $this->info("Dataset [{$this->dataSet}] {$table}.json");
            return json_decode(file_get_contents($file), true)
                ?: [];
        }
        return [];
    }

    /**
     * @param $tableToCheck
     */
    protected function checkSequence($tableToCheck)
    {
        if (DB::connection()->getName() == 'pgsql') {

            $highestId = DB::table($tableToCheck)->select( DB::raw('MAX(id)') )->first();
            $nextId    = DB::table($tableToCheck)->select( DB::raw("nextval('{$tableToCheck}_id_seq')") )->first();

            if($nextId && $highestId && $nextId->nextval < $highestId->max)
            {
                DB::select("SELECT setval('{$tableToCheck}_id_seq', {$highestId->max})");
                $highestId  = DB::table($tableToCheck)->select(DB::raw('MAX(id)'))->first();
                $nextId     = DB::table($tableToCheck)->select(DB::raw("nextval('{$tableToCheck}_id_seq')"))->first();
                if($nextId->nextval > $highestId->max)
                {
                    $this->info($tableToCheck . '_id_seq sequence corrected');
                }
                else
                {
                    $this->error('Error! The nextval sequence is still all screwed up on ' . $tableToCheck . '_id_seq');
                }
            }
        }
    }

    /**
     * @param $model
     */
    protected function clearByModel($model)
    {
        if (class_exists($model)) {
            /**
             * @var \App\Framework\Model $instance
             */
            $instance = new $model();
            if ($instance instanceof \App\Framework\Model) {
                $table = $instance->getTable();

                $this->info($model . ' clearing ' . $table);
                try {
                    $model::where('id', '>', 0)->delete();
                } catch (\Exception $e) {
                    $this->error('Unable to clear table. Constraints restricted.');
                }
            }
        }
    }

    /**
     * @param $model
     * @param bool $update
     * @param bool $delete
     * @param bool $ifOutdated
     * @return array
     */
    protected function byModel($model, $update=false, $delete=false, $ifOutdated=true)
    {
        return $this->byModelCallback($model, function($model, $object, $update){

            /**
             * TODO: КРИТИЧНЫЙ БАГ! учесть про создании id сущности.
             */
            if ($update) {
                $model::updateOrCreate([
                    'id'     => $object['id'],
                ], $object);
            } else {
                $model::firstOrCreate([
                    'id'     => $object['id'],
                ], $object);
            }

        }, $update, $delete, $ifOutdated);
    }

    protected function byModelCallback($model, callable $callback, $update=false, $delete=false, $ifOutdated=true)
    {
        if ($ifOutdated && !$this->isOutdatedModel($model, true)) {
            return null;
        }

        $ids = [];

        if (class_exists($model)) {
            /**
             * @var \App\Framework\Model $instance
             */
            $instance = new $model();
            if ($instance instanceof \App\Framework\Model) {

                $table = $instance->getTable();
                $objects = $this->sourceJson($table);
                $size = sizeof($objects);

                $fields = \DB::connection()->getSchemaBuilder()->getColumnListing($table);
                $this->info($model . ' -> ' . $table . "({$size})");

                if ($size > 0) {

                    if ($delete) {
                        $this->clearByModel($model);
                    }

                    $bar = $this->command->getOutput()->createProgressBar($size);
                    $bar->setOverwrite(true);
                    $bar->setFormat("[%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% [mem_usage: %memory%] ");
                    $bar->start();

                    foreach ($objects as $object) {

                        if ($fields) {
                            $object = array_only($object, $fields);
                        }

                        try {
                            $callback($model, $object, $update);
                        } catch (\Exception $e) {
                            $this->error($e->getMessage());
                        }

                        $bar->advance(1);
                        $ids[] = $object['id'];
                    }

                    $bar->finish();
                    $this->info($model . ' -> ' . $table . ' done!');
                }
                $this->checkSequence($table);
            } else {
                $this->error("Class {$model} is not valid Model");
            }
        } else {
            $this->error("Class {$model} not exists");
        }

        return $ids;
    }

    /**
     * @param $message
     */
    protected function info($message)
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * @param $message
     */
    protected function error($message)
    {
        if ($this->command) {
            $this->command->error($message);
        }
    }
}