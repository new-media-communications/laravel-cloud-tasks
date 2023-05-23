<?php

namespace Nmc\CloudTasks\Http\Controllers;

use Nmc\CloudTasks\CloudTasksJob;
use Nmc\CloudTasks\CloudTasksJobHandler;
use Nmc\CloudTasks\CloudTasksJobParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CloudTasksHandler extends Controller
{
    public function __invoke(Request $request)
    {
        CloudTasksJobHandler::withJob(
            CloudTasksJob::fromParams(CloudTasksJobParams::fromRequest($request))
        )->process();

        return response('JobCompleted', 200);
    }
}
