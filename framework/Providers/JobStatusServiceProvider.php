<?php

namespace CDeep\Providers;

use Carbon\Carbon;
use CDeep\Models\JobStatus;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class JobStatusServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        // Add Event listeners
        app(QueueManager::class)->before(function (JobProcessing $event) {
            if (!$this->isTrackable($event->job)) {
                return;
            }
            $this->updateJobStatus($event->job, [
                'status' => 'executing',
                'job_id' => $event->job->getJobId(),
                'attempts' => $event->job->attempts(),
                'queue' => $event->job->getQueue(),
                'started_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->after(function (JobProcessed $event) {
            if (!$this->isTrackable($event->job)) {
                return;
            }
            $this->updateJobStatus($event->job, [
                'status' => 'finished',
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->failing(function (JobFailed $event) {
            if (!$this->isTrackable($event->job)) {
                return;
            }
            $this->updateJobStatus($event->job, [
                'status' => 'failed',
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->exceptionOccurred(function (JobExceptionOccurred $event) {
            if (!$this->isTrackable($event->job)) {
                return;
            }
            $this->updateJobStatus($event->job, [
                'status' => 'failed',
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now(),
                'output' => json_encode(['message' => $event->exception->getMessage()])
            ]);
        });
    }

    /**
     * @param Job $job
     * @return bool
     */
    private function isTrackable(Job $job)
    {
        $jobStatus = $this->getJob($job);
        if (!is_callable([$jobStatus, 'isJobStatusInitialized'])) {
            return false;
        }

        return $jobStatus->isJobStatusInitialized();
    }

    /**
     * @param Job $job
     * @return mixed
     */
    private function getJob(Job $job)
    {
        return unserialize($job->payload()['data']['command'] ?? null);
    }

    /**
     * @param Job $job
     * @param array $data
     * @return bool|void
     */
    private function updateJobStatus(Job $job, array $data)
    {
        try {
            $jobStatus = $this->getJob($job);
            if (!is_callable([$jobStatus, 'getJobStatusId'])) {
                return;
            }

            $jobStatusId = $jobStatus->getJobStatusId();

            $jobStatus = JobStatus::where('id', '=', $jobStatusId);
            return $jobStatus->update($data);
        } catch (\Exception $e) {
            Log::error("{$e->getMessage()} [{$e->getFile()}:{$e->getLine()}]");
        }
    }
}
