<?php

namespace Nmc\CloudTasks;

use Nmc\CloudTasks\Exceptions\JobFailedException;
use Nmc\CloudTasks\Exceptions\JobReleasedException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;

class CloudTasksJobHandler
{
    private CloudTasksJob $job;

    public function __construct(CloudTasksJob $job)
    {
        $this->job = $job;
    }

    /**
     * @return self
     */
    public static function withJob(CloudTasksJob $job): self
    {
        return new self($job);
    }

    /**
     * @throws JobFailedException
     * @throws JobReleasedException
     */
    public function process(): void
    {
        $this->listenForEvents();

        try {
            $this
                ->worker()
                ->process($this->connection(), $this->job, new WorkerOptions());
        } catch (\Throwable $th) {
            app(ExceptionHandler::class)->report($th);

            throw JobFailedException::new();
        }

        if ($this->job->isReleased()) {
            throw JobReleasedException::new();
        }
    }

    private function connection(): string
    {
        return $this->job->getCloudTaskParams()->connectionName ?: config('queue.default');
    }

    private function worker(): Worker
    {
        /**
         * @var Worker $worker
         */
        $worker = app('queue.worker');

        return $worker
            ->setName($this->workerName())
            ->setCache(app('cache.store'));
    }

    private function workerName(): string
    {
        return $this->job->getCloudTaskParams()->queueName;
    }

    private function listenForEvents(): void
    {
        app('events')->listen(JobFailed::class, function (JobFailed $event) {
            app('queue.failer')->log(
                $event->connectionName,
                $event->job->getQueue(),
                $event->job->getRawBody(),
                $event->exception
            );
        });
    }
}
