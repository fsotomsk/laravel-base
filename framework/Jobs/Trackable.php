<?php

namespace CDeep\Jobs;

use CDeep\Models\JobStatus;

trait Trackable
{
    /**
     * @var int
     */
    protected $statusId;

    /**
     * @var bool
     */
    public $statusInitialized = false;

    protected function setProgressMax($value)
    {
        $this->update(['progress_max' => $value]);
    }

    protected function setProgressNow($value, $every = 1)
    {
        if ($value % $every == 0) {
            $this->update(['progress_now' => $value]);
        }
    }

    protected function setInput(array $value)
    {
        $this->update(['input' => $value]);
    }

    protected function setOutput(array $value)
    {
        $this->update(['output' => $value]);
    }

    protected function update(array $data)
    {
        $task = JobStatus::find($this->statusId);

        if ($task != null) {
            return $task->update($data);
        }
    }

    protected function prepareStatus($input=null)
    {
        if (!$this->statusId) {
            $status = JobStatus::create([
                'type'  => static::class,
                'queue' => $this->queue,
                'input' => $input,
            ]);
            $this->statusId = $status->id;
            $this->statusInitialized = true;
        }
    }

    /**
     * @return bool
     */
    public function isJobStatusInitialized()
    {
        return !empty($this->statusId);
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getJobStatusId()
    {
        if ($this->statusId == null) {
            throw new \Exception("Failed to get jobStatusId, have you called \$this->prepareStatus() in __construct() of Job?");
        }

        return $this->statusId;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     */
    public function getJobStatus()
    {
        return JobStatus::find(
            $this->getJobStatusId()
        );
    }
}
