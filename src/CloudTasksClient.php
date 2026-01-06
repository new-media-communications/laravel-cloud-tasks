<?php

namespace Nmc\CloudTasks;

use Google\Cloud\Tasks\V2\Client\CloudTasksClient as GoogleCloudTasksClient;

class CloudTasksClient
{
    private GoogleCloudTasksClient $cloudTask;

    /**
     * @param string|array|null $credentials
     */
    public function __construct($credentials = null)
    {
        $this->cloudTask = new GoogleCloudTasksClient([
            'credentials' => $credentials,
        ]);
    }

    public function cloudTask(): GoogleCloudTasksClient
    {
        return $this->cloudTask;
    }
}
