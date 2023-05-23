<?php

namespace Nmc\CloudTasks;

use Illuminate\Support\Facades\Queue;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CloudTasksServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cloud-tasks')
            ->hasConfigFile()
            ->hasRoute('api');
    }

    /**
     * @return void
     */
    public function packageBooted()
    {
        if (! config('queue.connections.cloud-tasks')) {
            config(['queue.connections.cloud-tasks' => config('cloud-tasks.queue')]);
        }

        Queue::addConnector('cloud-tasks', fn () => new CloudTasksConnector);
    }
}
