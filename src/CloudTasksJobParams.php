<?php

namespace Nmc\CloudTasks;

use Google\Cloud\Tasks\V2\Client\CloudTasksClient;
use Illuminate\Http\Request;

class CloudTasksJobParams
{
    public string $queueName;
    public string $taskName;
    public int $taskRetryCount;
    public int $taskExecutionCount;
    public int $taskScheduleTime;
    public ?int $taskPreviousResponseCode;
    public ?string $taskRetryReason;
    public string $connectionName;
    public string $rawPayload;
    public string $fullTaskName;
    public string $fullQueueName;

    /**
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self($request);
    }

    private function __construct(Request $request)
    {
        $this->taskRetryCount = (int) $request->header('X-CloudTasks-TaskRetryCount');
        $this->taskExecutionCount = (int) $request->header('X-CloudTasks-TaskExecutionCount');
        $this->taskScheduleTime = (int) $request->header('X-CloudTasks-TaskETA');
        $this->taskPreviousResponseCode = $request->header('X-CloudTasks-TaskPreviousResponse')
            ? (int) $request->header('X-CloudTasks-TaskPreviousResponse')
            : null;
        $this->taskRetryReason = (string) $request->header('X-CloudTasks-TaskRetryReason');
        $this->connectionName = (string) $request->input("jobConfig.connectionName", "");
        $this->rawPayload = (string) $request->input("jobPayload", "");

        $this->taskName = (string) $request->header('X-CloudTasks-TaskName');
        $this->queueName = (string) $request->header('X-CloudTasks-QueueName');

        $this->fullTaskName = CloudTasksClient::taskName(
            (string) config("queue.connections.{$this->connectionName}.project"),
            (string) config("queue.connections.{$this->connectionName}.location"),
            $this->queueName,
            $this->taskName
        );

        $this->fullQueueName = CloudTasksClient::queueName(
            (string) config("queue.connections.{$this->connectionName}.project"),
            (string) config("queue.connections.{$this->connectionName}.location"),
            $this->queueName
        );
    }
}
