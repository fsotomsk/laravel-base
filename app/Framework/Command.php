<?php

namespace App\Framework;

use Illuminate\Console\Command as CCommand;
use Illuminated\Console\Mutex;
use Illuminated\Console\WithoutOverlapping;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends CCommand
{
    use WithoutOverlapping;

    protected $mutexStrategy = 'file';
    protected $mutexFile     =  null;
    protected $mutexTimeout  =  0;
    protected $mutexVerbose  = true;

    /**
     * @var Mutex
     */
    protected $mutex = null;

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

    public function getMutexName()
    {
        return "icmutex-" . md5(
                $this->getName()
                . json_encode($this->options())
                . json_encode($this->arguments())
            );
    }

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

}