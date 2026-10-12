<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AtomicPasswordReset
{
    public function reset(#[\SensitiveParameter] array $credentials, Closure $callback): string
    {
        $broker = Password::broker();
        $user = $broker->getUser($credentials);
        if (! $user) {
            return Password::INVALID_USER;
        }

        $config = config('auth.passwords.'.config('auth.defaults.passwords'));
        $connection = DB::connection($config['connection'] ?? null);
        $table = $config['table'];
        // Snapshot before broker validation; the conditional DELETE below is the claim,
        // not this unlocked read or the transaction by itself.
        $row = $connection->table($table)->where('email', $user->getEmailForPasswordReset())->first();
        if (! $row || ! Hash::check($credentials['token'], $row->token)
            || ! $broker->tokenExists($user, $credentials['token'])) {
            return Password::INVALID_TOKEN;
        }

        return $connection->transaction(function () use ($connection, $table, $row, $user, $credentials, $callback) {
            $claimed = $connection->table($table)->where('email', $row->email)
                ->where('token', $row->token)->where('created_at', $row->created_at)->delete();
            if ($claimed !== 1) {
                return Password::INVALID_TOKEN;
            }
            $callback($user, $credentials['password']);

            return Password::PASSWORD_RESET;
        });
    }
}
