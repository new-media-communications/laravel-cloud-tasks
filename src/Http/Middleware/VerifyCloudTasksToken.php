<?php

namespace Nmc\CloudTasks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Nmc\CloudTasks\CloudTaskCredentials;

class VerifyCloudTasksToken
{
    /**
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $verify = $this->verifyIdToken($request);
        abort_unless($verify, 401);

        return $next($request);
    }

    private function verifyIdToken(Request $request): bool
    {
        $config = $this->getConfig($request);

        if (!$config) {
            return true;
        }

        if (!($config['auth'] ?? null)) {
            return true;
        }

        if (!($credentials = $config['credentials'] ?? null)) {
            return false;
        }

        $client = new \Google\Client();
        $client->setAuthConfig(CloudTaskCredentials::parse($credentials));

        try {
            $payload = $client->verifyIdToken($this->getToken($request));

            return Arr::get($payload, 'aud') === Arr::get($config, 'auth.audience');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getToken(Request $request): ?string
    {
        $authorization = $request->header('authorization');
        if (!$authorization) {
            return null;
        }

        if (!is_string($authorization)) {
            return null;
        }

        return trim(str_replace('Bearer ', '', $authorization));
    }

    private function getConfig(Request $request): ?array
    {
        $connectionName = $request->input("jobConfig.connectionName");
        if (!$connectionName) {
            return null;
        }

        return config("queue.connections.{$connectionName}");
    }
}
