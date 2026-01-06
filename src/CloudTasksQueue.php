<?php

namespace Nmc\CloudTasks;

use Google\ApiCore\ApiException;
use Google\Cloud\Tasks\V2\CreateTaskRequest;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\OidcToken;
use Google\Cloud\Tasks\V2\PurgeQueueRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Timestamp;
use Illuminate\Contracts\Queue\ClearableQueue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;

class CloudTasksQueue extends Queue implements QueueContract, ClearableQueue
{
    private CloudTasksClient $cloudTask;
    private string $default;
    private string $project;
    private string $location;
    private string $handlerUrl;
    private array $auth;

    public function __construct(
        CloudTasksClient $cloudTask,
        string $default,
        string $project,
        string $location,
        string $handlerUrl,
        array $auth,
        bool $dispatchAfterCommit = false
    ) {
        $this->cloudTask = $cloudTask;
        $this->default = $default;
        $this->project = $project;
        $this->location = $location;
        $this->handlerUrl = $handlerUrl;
        $this->auth = $auth;
        $this->dispatchAfterCommit = $dispatchAfterCommit;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null): int
    {
        return 0;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string|object  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue ?: $this->default, $data),
            $queue ?: $this->default,
            null,
            function ($payload, $queue) {
                return $this->pushRaw($payload, $queue);
            }
        );
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->createTask($payload, $queue)->getName();
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->enqueueUsing(
            $job,
            $this->createPayload($job, $queue ?: $this->default, $data),
            $queue ?: $this->default,
            $delay,
            function ($payload, $queue, $delay) {
                return $this->createTask($payload, $queue, $this->secondsUntil($delay))->getName();
            }
        );
    }

    /**
     * Pop the next job off of the queue.
     *
     * Note: cloud task is a push only queue so we dont need to pop jobs from queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        return null;
    }

    /**
     * Delete all of the jobs from the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function clear($queue)
    {
        return tap($this->size($queue), function () use ($queue) {
            try {
                $this->cloudTask->cloudTask()->purgeQueue(
                    PurgeQueueRequest::build($this->getQueueName($queue))
                );
            } catch (ApiException $th) {
                throw $th; // TODO: handle exception
            } finally {
                $this->cloudTask->cloudTask()->close();
            }
        });
    }

    public function getQueueName(?string $queue = null): string
    {
        return $this->cloudTask->cloudTask()::queueName($this->project, $this->location, $queue ?: $this->default);
    }

    /**
     * @param null|string $queue
     */
    private function createTask(string $payload, ?string $queue = null, int $delay = 0): Task
    {
        $request = (new HttpRequest())
            ->setUrl($this->handlerUrl)
            ->setHttpMethod(HttpMethod::POST)
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->setBody(json_encode([
                'jobPayload' => $payload,
                'jobConfig' => [
                    'connectionName' => $this->connectionName,
                ],
            ]));

        if (($email = $this->auth['service_account_email'] ?? null) && ($audience = $this->auth['audience'] ?? null)) {
            $oidcToken = (new OidcToken())
                ->setServiceAccountEmail($email)
                ->setAudience($audience);
            $request->setOidcToken($oidcToken);
        }

        $task = (new Task())->setHttpRequest($request);

        if (time() < ($availableAt = $this->availableAt($delay))) {
            $task->setScheduleTime(
                (new Timestamp())->setSeconds($availableAt),
            );
        }

        try {
            return $this->cloudTask->cloudTask()->createTask(CreateTaskRequest::build(
                $this->getQueueName($queue),
                $task
            ));
        } catch (ApiException $th) {
            throw $th; // TODO: handle exception
        } finally {
            $this->cloudTask->cloudTask()->close();
        }
    }
}
