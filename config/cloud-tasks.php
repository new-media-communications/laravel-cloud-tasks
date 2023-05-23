<?php

use Nmc\CloudTasks\Http\Middleware\VerifyCloudTasksToken;

return [
    'middleware' => ['api', VerifyCloudTasksToken::class],
    'queue' => [
        'driver' => 'cloud-tasks',
        'project' => env('CLOUD_TASKS_PROJECT_ID'),
        'location' => env('CLOUD_TASKS_LOCATION'),
        'queue' => env('CLOUD_TASKS_QUEUE', 'default'),
        'handler_url' => env('CLOUD_TASKS_HANDLER_URL', env('APP_URL') . '/cloud-tasks-handler'),
        'credentials' => env('CLOUD_TASKS_CREDENTIALS', config_path('credentials.json')),
        'auth' => [
            'service_account_email' => env('CLOUD_TASKS_SERVICE_ACCOUNT_EMAIL'),
            'audience' => env('CLOUD_TASKS_AUDIENCE'),
        ],
        'after_commit' => false,
    ]
];
