<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class AdminSyncCommand extends Command
{
    protected $signature = 'admin:sync';

    protected $description = 'Create or synchronize the configured administrator account without exposing credentials.';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) config('farzin.admin.email', '')));
        $password = (string) config('farzin.admin.password', '');
        $name = trim((string) config('farzin.admin.name', 'Farzin Admin'));

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

        // The User model's "hashed" cast hashes this plain configuration value
        // exactly once. Never print or log the credential.
        User::updateOrCreate(
            ['id' => $admin?->getKey()],
            [
                'name' => $name !== '' ? $name : 'Farzin Admin',
                'email' => $email,
                'role' => 'admin',
                'password' => Hash::make($password),
            ]
        );

        $this->info('حساب مدیر با تنظیمات محیط همگام شد.');

        return self::SUCCESS;
    }
}
