<?php

namespace CDeep\Jobs;


trait Status
{
    protected function dispatch()
    {
        $job   = parent::dispatch();
        $jobId = $job->getJobId();
        echo "jonId: [{$jobId}]";
    }
}
