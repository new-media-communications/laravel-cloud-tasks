# Google Cloud Tasks For Laravel


[![Latest Version on Packagist](https://img.shields.io/packagist/v/nmc/laravel-cloud-tasks.svg?style=flat-square)](https://packagist.org/packages/nmc/laravel-cloud-tasks)
[![Total Downloads](https://img.shields.io/packagist/dt/nmc/laravel-cloud-tasks.svg?style=flat-square)](https://packagist.org/packages/nmc/laravel-cloud-tasks)


## Installation

You can install the package via composer:

```bash
composer require nmc/laravel-cloud-tasks
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Nmc\CloudTasks\CloudTasksServiceProvider" --tag="cloud-tasks-config"
```

This is the contents of the published config file:

```php
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
```

## Credits

- [Fitim Vata](https://github.com/fitimvata)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
