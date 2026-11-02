<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AdminSyncCommand extends Command
{
    protected $signature = 'admin:sync';

    protected $description = 'Create or synchronize the configured administrator account without exposing credentials.';

    public function handle(): int
    {
        try {
            $credentials = AdminEnvironment::credentials();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $email = $credentials['email'];
        $password = $credentials['password'];
        $name = $credentials['name'];

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->error('ADMIN_EMAIL و ADMIN_PASSWORD باید در محیط برنامه تنظیم شده باشند.');

            return self::FAILURE;
        }

        if (mb_strlen($password) < 12) {
            $this->error('رمز مدیر باید حداقل ۱۲ نویسه داشته باشد.');

            return self::FAILURE;
        }

        $emailOwner = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if ($emailOwner && ! $emailOwner->isAdmin()) {
            $this->error('این ایمیل متعلق به حساب غیرمدیر است؛ هیچ حسابی تغییر نکرد.');

            return self::FAILURE;
        }

        $admin = $emailOwner;

        if (! $admin) {
            $existingAdmins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->get();

            if ($existingAdmins->count() > 1) {
                $this->error('چند حساب مدیر وجود دارد و ایمیل تنظیم‌شده با هیچ‌کدام تطبیق ندارد؛ همگام‌سازی متوقف شد.');

                return self::FAILURE;
            }

            $admin = $existingAdmins->first();
        }

        $attributes = [
            'name' => $name !== '' ? $name : 'Farzin Admin',
            'email' => $email,
            'role' => 'admin',
        ];

        $passwordMatches = false;

        if ($admin && filled($admin->password)) {
            try {
                $passwordMatches = Hash::check($password, (string) $admin->password);
            } catch (Throwable) {
                $passwordMatches = false;
            }
        }

        // Hash only when creating the account or rotating its password. The
        // User model's "hashed" cast recognizes this hash and will not hash it twice.
        if (! $passwordMatches) {
            $attributes['password'] = Hash::make($password);
        }

        User::updateOrCreate(
            ['id' => $admin?->getKey()],
            $attributes
        );

        $this->info('حساب مدیر با تنظیمات محیط همگام شد.');

        return self::SUCCESS;
    }
}
