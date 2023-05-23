<?php

namespace Nmc\CloudTasks;

use Google\Cloud\Tasks\V2\CloudTasksClient as GoogleCloudTasksClient;
use Google\Cloud\Tasks\V2beta3\CloudTasksClient as  GoogleV2beta3CloudTasksClient;

class CloudTasksClient
{
    private GoogleCloudTasksClient $cloudTask;
    private GoogleV2beta3CloudTasksClient $cloudTaskBeta;

    /**
     * @param string|array|null $credentials
     */
    public function __construct($credentials = null)
    {
        $this->cloudTask = new GoogleCloudTasksClient([
            'credentials' => $credentials,
        ]);

        $this->cloudTaskBeta = new GoogleV2beta3CloudTasksClient([
            'credentials' => $credentials,
        ]);
    }

    public function cloudTaskBeta(): GoogleV2beta3CloudTasksClient
    {
        return $this->cloudTaskBeta;
    }

    public function cloudTask(): GoogleCloudTasksClient
    {
        return $this->cloudTask;
    }
}
