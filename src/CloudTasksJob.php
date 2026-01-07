<?php

namespace Nmc\CloudTasks;

use Google\Cloud\Tasks\V2\GetQueueRequest;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

class CloudTasksJob extends Job implements JobContract
{
    protected CloudTasksClient $cloudTask;

    private CloudTasksJobParams $cloudTaskParams;

    /**
     * @return self
     */
    public static function fromParams(CloudTasksJobParams $params): self
    {
        return new self(
            Container::getInstance(),
            new CloudTasksClient(CloudTaskCredentials::parse(config("queue.connections.{$params->connectionName}.credentials"))),
            $params
        );
    }

    public function __construct(Container $container, CloudTasksClient $cloudTask, CloudTasksJobParams $cloudTaskParams)
    {
        $this->container = $container;
        $this->cloudTask = $cloudTask;
        $this->cloudTaskParams = $cloudTaskParams;
        $this->connectionName = $this->cloudTaskParams->connectionName;
        $this->queue = $this->cloudTaskParams->queueName;
    }

    /**
     * Note: cloud task is a push only queue so we dont need to release jobs to the queue. Also the backoff is handled in cloud task.
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
    }

    /**
     * Note: cloud task is a push only queue so we dont need to delete jobs from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->cloudTaskParams->taskRetryCount + 1;
    }

    /**
     * Get the number of times to attempt a job.
     *
     * @return int|null
     */
    public function maxTries()
    {
        $maxTries = (int) Cache::remember("cloudtask:{$this->cloudTaskParams->fullQueueName}", Date::now()->addHour(1), function (): int {
            $maxAttempts = optional(
                $this->cloudTask
                    ->cloudTask()
                    ->getQueue(GetQueueRequest::build($this->cloudTaskParams->fullQueueName))
                    ->getRetryConfig()
            )->getMaxAttempts();

            return (int) ($maxAttempts === null ? -1 : $maxAttempts);
        });

        return $maxTries === -1 ? null : $maxTries;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->cloudTaskParams->taskName;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->cloudTaskParams->rawPayload;
    }

    public function getCloudTaskParams(): CloudTasksJobParams
    {
        return $this->cloudTaskParams;
    }
}
