<?php

namespace CDeep\Jobs;


trait Status
{
    protected function dispatchWithStatus()
    {
        $job   = $this->dispatch($this);
        $jobId = $job->getJobId();
        echo "jonId: [{$jobId}]";
    }
}
