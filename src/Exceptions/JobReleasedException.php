<?php

namespace Nmc\CloudTasks\Exceptions;

use Exception;

class JobReleasedException extends Exception
{
    /**
     * @return self
     */
    public static function new(): self
    {
        return new self("Job released to the queue.");
    }

    /**
     * @return null
     */
    public function report()
    {
        return null;
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function render()
    {
        return response(class_basename($this), 400);
    }
}
