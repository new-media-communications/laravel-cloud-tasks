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
            'enabled' => env('CLOUD_TASKS_AUTH_ENABLED', true),
            'allowed_emails' => env('CLOUD_TASKS_AUTH_ALLOWED_EMAILS', null),
            'service_account_email' => env('CLOUD_TASKS_SERVICE_ACCOUNT_EMAIL'),
            'audience' => env('CLOUD_TASKS_AUDIENCE'),
        ],
        'after_commit' => false,
    ]
];
```

## Credentials

The `credentials` config value is optional. Set it to a service-account key file
path (or JSON string) to authenticate explicitly, or omit it to fall back to
[Application Default Credentials](https://cloud.google.com/docs/authentication/application-default-credentials)
— the queue no longer errors when the value is absent.

## Authentication

When `auth.enabled` is `true` (the default), each task is dispatched with an
[OIDC token](https://cloud.google.com/tasks/docs/creating-http-target-tasks#token)
so Google Cloud Tasks signs its request to your handler, and the
`VerifyCloudTasksToken` middleware rejects any request that isn't signed by an
allowed service account.

| Env var | Description |
| --- | --- |
| `CLOUD_TASKS_AUTH_ENABLED` | Enable OIDC signing on dispatch and verification on the handler. Defaults to `true`. Set to `false` to disable auth entirely. |
| `CLOUD_TASKS_AUTH_ALLOWED_EMAILS` | Comma-separated list of service-account emails allowed to invoke the handler. When empty, the application's own [default service account](https://cloud.google.com/docs/authentication/application-default-credentials) is used. |
| `CLOUD_TASKS_SERVICE_ACCOUNT_EMAIL` | Service account used to mint the OIDC token when dispatching a task. Defaults to the application default credentials identity. |
| `CLOUD_TASKS_AUDIENCE` | Audience (`aud`) claim for the OIDC token. Defaults to the handler URL. |

> **Upgrading:** auth is now enabled by default. If your app relied on the
> handler being unauthenticated, set `CLOUD_TASKS_AUTH_ENABLED=false`, or make
> sure Application Default Credentials are available so a service-account
> identity can be resolved on both ends.

## Credits

- [Fitim Vata](https://github.com/fitimvata)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
