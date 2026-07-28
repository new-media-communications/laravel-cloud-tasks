<?php

namespace Nmc\CloudTasks\Http\Middleware;

use Closure;
use Google\Client;
use Illuminate\Http\Request;
use Nmc\CloudTasks\CloudTasksQueue;
use Throwable;

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
        $connectionName = $request->input("jobConfig.connectionName");
        if (!$connectionName) {
            return false;
        }

        $config = config("queue.connections.{$connectionName}");
    
        if (!$config) {
            return false;
        }

        $auth_config = $config['auth'] ?? null;

        if (!$auth_config) {
            return true;
        }

        if (!($auth_config['enabled'] ?? false)) {
            return true;
        }

        $allowed_emails = array_values(
            array_filter(
                array_map(fn($item) => trim($item), explode(',', $auth_config['allowed_emails'] ?? ''))
            )
        ) ?: array_filter([CloudTasksQueue::defaultServiceAccountEmail()]);

        try {
            $client = new Client();
            $client->setClientId(($auth_config['audience'] ?? null) ?: $request->url());

            $payload = $client->verifyIdToken($this->authorization($request));

            if (!$payload) {
                return false;
            }

            return in_array($payload['email'], $allowed_emails, true);
        } catch (Throwable) {
            return false;
        }
    }

    private function authorization(Request $request): ?string
    {
        $authorization = $request->header('authorization');

        if (!$authorization) {
            return null;
        }

        return trim(str_replace('Bearer ', '', $authorization));
    }
}
