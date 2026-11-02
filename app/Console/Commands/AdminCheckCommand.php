<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AdminCheckCommand extends Command
{
    protected $signature = 'admin:check';

    protected $description = 'Check configured administrator credentials without displaying them.';

    public function handle(): int
    {
        try {
            $credentials = AdminEnvironment::credentials();
        } catch (Throwable) {
            $this->line('admin_exists=false');
            $this->line('email_matches=false');
            $this->line('password_matches=false');

            return self::FAILURE;
        }

        $email = $credentials['email'];
        $password = $credentials['password'];

        $user = $email === ''
            ? null
            : User::query()
                ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                ->first();

        $exists = $user?->isAdmin() === true;
        $emailMatches = $exists
            && mb_strtolower(trim((string) $user->email)) === $email;
        try {
            $passwordMatches = $exists
                && $password !== ''
                && Hash::check($password, (string) $user->password);
        } catch (Throwable) {
            $passwordMatches = false;
        }

        $this->line('admin_exists=' . ($exists ? 'true' : 'false'));
        $this->line('email_matches=' . ($emailMatches ? 'true' : 'false'));
        $this->line('password_matches=' . ($passwordMatches ? 'true' : 'false'));

        return $exists && $emailMatches && $passwordMatches
            ? self::SUCCESS
            : self::FAILURE;
    }
}
