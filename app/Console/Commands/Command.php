<?php

namespace App\Console\Commands;

use Illuminate\Console\Command as ICommand;

class Command extends ICommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmd';

    /**
     * @var null
     */
    protected $hLockFile = null;

    /**
     * @param int $count
     * @return \Closure
     */
    protected function bar($count=0)
    {
        if (property_exists($this, 'output')) {
            $bar = $this->output->createProgressBar($count);
            $bar->setOverwrite(true);
            $bar->setFormat("[%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% [mem_usage: %memory%] ");
            $bar->start();

            return (function ($finish=false) use ($bar) {
                if ($finish) {
                    $bar->finish();
                } else {
                    $bar->advance();
                }
            });
        }
        return (function(){});
    }

    /**
     * @param string $id
     * @return bool
     */
    protected function isLocked($id='')
    {
        $signature = str_replace(':', '-', explode(' ', $this->signature, 2)[0]);
        $fileName = storage_path("{$signature}{$id}.lock");

        if (!$this->hLockFile) {
            $this->hLockFile = fopen($fileName, "w");
        }

        $lock = flock($this->hLockFile, LOCK_EX | LOCK_NB);
        if ($lock) {
            register_shutdown_function(function() use ($fileName) {
                fclose($this->hLockFile);
                unlink($fileName);
            });
        }

        return !$lock;
    }

    /**
     * @return $this
     */
    public function fork()
    {
        if (function_exists('pcntl_fork')) {
            $pid = pcntl_fork();
            if ($pid > 0) {
                $this->info('Fork ' . $pid);
                pcntl_wait($status);
            }
        }
        return $this;
    }
}