<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;

class Kernel extends \CDeep\Console\Kernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        parent::schedule($schedule);
        // $schedule->command('inspire')
        //          ->hourly();
    }
}
