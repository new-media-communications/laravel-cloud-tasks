<?php

namespace Nmc\CloudTasks;

class CloudTaskCredentials
{
    public static function parse(array|string|null $credentials): array|string|null
    {
        $maybeCrendentials = is_string($credentials)
            ? json_decode($credentials, true) : null;

        return is_array($maybeCrendentials) ? $maybeCrendentials : $credentials;
    }
}
