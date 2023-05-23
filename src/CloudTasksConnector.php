<?php

namespace Nmc\CloudTasks;

use Illuminate\Queue\Connectors\ConnectorInterface;

class CloudTasksConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new CloudTasksQueue(
            new CloudTasksClient(CloudTaskCredentials::parse($config['credentials'])),
            $config['queue'],
            $config['project'],
            $config['location'],
            $config['handler_url'],
            $config['auth'] ?? [],
            $config['after_commit'] ?? false
        );
    }
}
