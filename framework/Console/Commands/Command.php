<?php

namespace CDeep\Console\Commands;


use Illuminate\Console\Command as CCommand;
use Illuminated\Console\Mutex;
use Illuminated\Console\WithoutOverlapping;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends CCommand
{
    use WithoutOverlapping;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmd';

    protected $mutexStrategy = 'file';
    protected $mutexFile     =  null;
    protected $mutexTimeout  =  0;
    protected $mutexVerbose  = true;

    /**
     * @var Mutex
     */
    protected $mutex = null;

    /**
     *
     */
    protected function initializeMutex()
    {
        $this->mutexFile = storage_path(implode(DIRECTORY_SEPARATOR, ["app","{$this->getMutexName()}.lock"]));
        $this->mutex = new Mutex($this);

        if ($this->mutex->acquireLock($this->getMutexTimeout())) {
            /**
             * Lock
             */
            if ($this->mutexVerbose) {
                $this->info("Mutex {$this->mutexFile} locked");
            }
            register_shutdown_function([$this, 'releaseMutexLock'], $this->mutex);
        } else {
            /**
             * Already running
             */
            $this->setCode(function () {
                $this->info("Command {$this->getName()} is running now!");
            });
        }
    }

    /**
     * @return string
     */
    public function getMutexName()
    {
        return "icmutex-" . md5(
                $this->getName()
                . json_encode($this->options())
                . json_encode($this->arguments())
            );
    }

    /**
     * @param Mutex $mutex
     */
    public function releaseMutexLock(Mutex $mutex)
    {
        try {
            $mutex->releaseLock();
            @unlink($this->mutexFile);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Execute the console command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $out = parent::execute($input, $output);
        if ($this->mutex instanceof Mutex) {
            $this->releaseMutexLock($this->mutex);
            if ($this->mutexVerbose) {
                $this->info('Mutex released');
            }
        }
        return $out;
    }

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