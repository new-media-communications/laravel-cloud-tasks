<?php

use Nmc\CloudTasks\Http\Controllers\CloudTasksHandler;
use Illuminate\Support\Facades\Route;

Route::post("cloud-tasks-handler", CloudTasksHandler::class)
    ->name('cloud-tasks-handler')
    ->middleware(config('cloud-tasks.middleware'));