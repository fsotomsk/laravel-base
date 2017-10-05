<?php

namespace CDeep\Console\Commands\Server;


use CDeep\Console\Commands\Command;

class Chmod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:chmod';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup chmod specific paths';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $paths = [
            base_path('storage'),
            base_path('storage/app'),
            base_path('storage/app/public'),
            base_path('storage/framework'),
            base_path('storage/framework/cache'),
            base_path('storage/framework/sessions'),
            base_path('storage/framework/testing'),
            base_path('storage/framework/views'),
            base_path('storage/logs'),
            base_path('storage/logs/laravel.log'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                chmod($path, 0666);
            } elseif (is_dir($path)) {
                chmod($path, 0777);
            }
        }
        return 0;
    }
}
