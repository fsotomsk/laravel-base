<?php

namespace CDeep\Console\Commands\Server;


use CDeep\Console\Commands\Command;
use Symfony\Component\Process\Process;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize server environment.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        /**
         * sudoers (adding NOPASSWD)
         * chmod dirs (configs)
         * check dependencies and install it
         * creating user & db & grant access
         */

        if (!$this->isDebianLinux()) {
            $this->error('This command only for Debian Linux.');
            return 1;
        }

        if (!$this->isRoot()) {
            $this->error('Superuser privileges required! Please, run command as root.');
            return 1;
        }

        $this->installPhpExtensions();
        return 0;
    }

    /**
     * @return bool|int
     */
    protected function isDebianLinux()
    {
        $osReleaseFile = '/etc/os-release';
        if (!file_exists($osReleaseFile)) {
            return false;
        }
        return strpos(file_get_contents($osReleaseFile), 'ID=debian');
    }

    /**
     * @return bool
     */
    protected function isRoot()
    {
        return is_writable('/etc');
    }

    /**
     *
     */
    protected function installDependencies()
    {

    }

    protected function installPhpExtensions()
    {
        $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        $extensionRequired = [
            'zip'       => "php{$phpVersion}-zip",
            'mbstring'  => "php{$phpVersion}-mbstring",
            'PDO'       => "php{$phpVersion}-pdo",
            //'pdo_mysql' => "php{$phpVersion}-pdo-mysql",
            'pdo_pgsql' => "php{$phpVersion}-pdo-pgsql",
            'xml'       => "php{$phpVersion}-xml",
            'curl'      => "php{$phpVersion}-curl",
            'openssl'   => "php{$phpVersion}-openssl",
            'iconv'     => "php{$phpVersion}-iconv",
            'soap'      => "php{$phpVersion}-soap",
        ];

        $extensionInstalled = get_loaded_extensions();
        $extensionNeeded = array_diff($extensionRequired, array_keys($extensionInstalled));

        foreach ($extensionNeeded as $ext) {
            $this->exec("apt-get install -y {$extensionRequired[$ext]}");
        }

        return $extensionNeeded;
    }
}
